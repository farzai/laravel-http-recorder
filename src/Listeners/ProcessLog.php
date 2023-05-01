<?php

namespace Farzai\HttpRecorder\Listeners;

use Farzai\HttpRecorder\RequestRecorder;

class ProcessLog
{
    protected RequestRecorder $recorder;

    /**
     * ProcessLog constructor.
     */
    public function __construct(RequestRecorder $recorder)
    {
        $this->recorder = $recorder;
    }

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Foundation\Http\Events\RequestHandled  $event
     * @return void
     */
    public function handle($event)
    {
        if ($this->recorder->shouldRecord($event->request)) {
            $this->recorder->record($event->request, $event->response);
        }
    }
}
