<?php

namespace Farzai\HttpRecorder\Storage;

use Farzai\HttpRecorder\Contracts\EntryRepositoryInterface;
use Farzai\HttpRecorder\IncomingEntry;
use Illuminate\Support\Facades\DB;

class DatabaseEntryRepository implements EntryRepositoryInterface
{
    /**
     * The config.
     *
     * @var array
     */
    protected $config;

    /**
     * The database connection.
     *
     * @var string
     */
    protected $connection;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table;

    /**
     * Create a new instance.
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $this->connection = $config['connection'] ?? null;
        $this->table = $config['table'] ?? 'http_log_requests';
    }

    /**
     * Create a new entry.
     *
     * @return void
     */
    public function create(IncomingEntry $entry)
    {
        DB::connection($this->connection)
            ->table($this->table)
            ->insert([
                'causer_id' => $entry->causer['id'] ?? null,
                'causer_type' => $entry->causer['type'] ?? null,
                'ip_address' => $entry->ip_address,
                'uri' => $entry->uri,
                'method' => $entry->method,
                'controller_action' => $entry->controller_action,
                'middleware' => $entry->middleware ? json_encode($entry->middleware) : null,
                'headers' => json_encode($entry->headers),
                'payload' => $entry->payload ? json_encode($entry->payload) : null,
                'session' => $entry->session ? json_encode($entry->session) : null,
                'response_status' => $entry->response_status,
                'response_headers' => $entry->response_headers ? json_encode($entry->response_headers) : null,
                'response_body' => $entry->response_body
                    ? (is_array($entry->response_body) ? json_encode($entry->response_body) : $entry->response_body)
                    : null,
                'duration' => $entry->duration,
                'memory' => $entry->memory,
                'created_at' => $entry->created_at,
                'updated_at' => $entry->created_at,
            ]);
    }
}
