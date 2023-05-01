<?php

namespace Farzai\HttpRecorder\Events;

use Farzai\HttpRecorder\IncomingEntry;
use Illuminate\Queue\SerializesModels;

class RequestRecorded
{
    use SerializesModels;

    /**
     * The incoming entry.
     *
     * @var \Farzai\HttpRecorder\IncomingEntry
     */
    public $entry;

    /**
     * Create a new event instance.
     */
    public function __construct(IncomingEntry $entry)
    {
        $this->entry = $entry;
    }
}
