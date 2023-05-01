<?php

namespace Farzai\HttpRecorder;

use BackedEnum;
use Farzai\HttpRecorder\Jobs\RecordJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class RequestRecorder
{
    /**
     * @var array
     */
    protected $config;

    /**
     * RequestRecorder constructor.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Determine if the request should be recorded.
     */
    public function isEnable(): bool
    {
        return $this->config['enabled'];
    }

    /**
     * Record the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     */
    public function record($request, $response)
    {
        $startTime = defined('LARAVEL_START')
            ? LARAVEL_START
            : $request->server('REQUEST_TIME_FLOAT');

        $entry = new IncomingEntry([
            'causer' => $this->causer($request),
            'ip_address' => $request->ip(),
            'uri' => str_replace($request->root(), '', $request->fullUrl()) ?: '/',
            'method' => $request->method(),
            'controller_action' => optional($request->route())->getActionName(),
            'middleware' => array_values(optional($request->route())->gatherMiddleware() ?? []),
            'headers' => $this->headers($request->headers->all()),
            'payload' => $this->payload($this->input($request)),
            'session' => $this->payload($this->sessionVariables($request)),
            'response_status' => $response->getStatusCode(),
            'response_headers' => $this->headers($response->headers->all()),
            'response_body' => $this->response($response),
            'duration' => $startTime ? floor((microtime(true) - $startTime) * 1000) : null,
            'memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 1),
            'created_at' => (string) now()->subMicroseconds($startTime ?: 0),
        ]);

        RecordJob::dispatch($entry)
            ->onQueue(data_get($this->config, 'queue.name'))
            ->onConnection(data_get($this->config, 'queue.connection'));
    }

    /**
     * Get the causer of the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function causer($request)
    {
        $user = $request->user();

        if (! $user) {
            return [
                'id' => null,
                'type' => null,
            ];
        }

        $causer = [
            'id' => $user->getAuthIdentifier(),
            'type' => get_class($user),
        ];

        return $causer;
    }

    /**
     * Format the given headers.
     *
     * @param  array  $headers
     * @return array
     */
    protected function headers($headers)
    {
        $headers = collect($headers)->map(function ($header) {
            return $header[0];
        })->toArray();

        return $this->hideParameters($headers, data_get($this->config, 'sensitive.headers', []));
    }

    /**
     * Format the given payload.
     *
     * @param  array  $payload
     * @return array
     */
    protected function payload($payload)
    {
        return $this->hideParameters($payload, data_get($this->config, 'sensitive.body', []));
    }

    /**
     * Hide the given parameters.
     *
     * @param  array  $data
     * @param  array  $hidden
     * @return mixed
     */
    protected function hideParameters($data, $hidden)
    {
        $hidden = array_map(fn ($item) => strtolower($item), $hidden);

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $hidden)) {
                $data[$key] = '********';
            } elseif (is_array($value)) {
                $data[$key] = $this->hideParameters($value, $hidden);
            }
        }

        return $data;
    }

    /**
     * Extract the input from the given request.
     *
     * @return array
     */
    private function input(Request $request)
    {
        $files = $request->files->all();

        array_walk_recursive($files, function (&$file) {
            $file = [
                'name' => $file->getClientOriginalName(),
                'size' => $file->isFile() ? ($file->getSize() / 1000).'KB' : '0',
            ];
        });

        return array_replace_recursive($request->input(), $files);
    }

    /**
     * Format the given response object.
     *
     * @return array|string
     */
    protected function response(Response $response)
    {
        $content = $response->getContent();

        if (is_string($content)) {
            if (is_array(json_decode($content, true)) &&
                json_last_error() === JSON_ERROR_NONE) {
                return $this->contentWithinLimits($content)
                        ? $this->hideParameters(json_decode($content, true), data_get($this->config, 'sensitive.body', []))
                        : 'Purged By Http Recorder';
            }

            if (Str::startsWith(strtolower($response->headers->get('Content-Type') ?? ''), 'text/plain')) {
                return $this->contentWithinLimits($content) ? $content : 'Purged By Http Recorder';
            }
        }

        if ($response instanceof RedirectResponse) {
            return 'Redirected to '.$response->getTargetUrl();
        }

        if ($response instanceof IlluminateResponse && $response->getOriginalContent() instanceof View) {
            return [
                'view' => $response->getOriginalContent()->getPath(),
                'data' => $this->extractDataFromView($response->getOriginalContent()),
            ];
        }

        if (is_string($content) && empty($content)) {
            return 'Empty Response';
        }

        return 'HTML Response';
    }

    /**
     * Extract the session variables from the given request.
     *
     * @return array
     */
    private function sessionVariables(Request $request)
    {
        return $request->hasSession() ? $request->session()->all() : [];
    }

    /**
     * Determine if the content is within the set limits.
     *
     * @param  string  $content
     * @return bool
     */
    public function contentWithinLimits($content)
    {
        $limit = $this->config['size_limit'] ?? 64;

        return intdiv(mb_strlen($content), 1000) <= $limit;
    }

    /**
     * Extract the data from the given view in array form.
     *
     * @param  \Illuminate\View\View  $view
     * @return array
     */
    protected function extractDataFromView($view)
    {
        return collect($view->getData())->map(function ($value) {
            if ($value instanceof Model) {
                return $this->given($value);
            } elseif (is_object($value)) {
                return [
                    'class' => get_class($value),
                    'properties' => json_decode(json_encode($value), true),
                ];
            } else {
                return json_decode(json_encode($value), true);
            }
        })->toArray();
    }

    /**
     * Format the given model to a readable string.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    public function given($model)
    {
        if ($model instanceof Pivot && ! $model->incrementing) {
            $keys = [
                $model->getAttribute($model->getForeignKey()),
                $model->getAttribute($model->getRelatedKey()),
            ];
        } else {
            $keys = $model->getKey();
        }

        return get_class($model).':'.implode('_', array_map(function ($value) {
            return $value instanceof BackedEnum ? $value->value : $value;
        }, Arr::wrap($keys)));
    }
}
