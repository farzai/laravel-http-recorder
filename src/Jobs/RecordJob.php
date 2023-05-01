<?php

namespace Farzai\HttpRecorder\Jobs;

use Farzai\HttpRecorder\Contracts\EntryRepositoryInterface;
use Farzai\HttpRecorder\Events\RequestRecorded;
use Farzai\HttpRecorder\IncomingEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The incoming entry.
     *
     * @var \Farzai\HttpRecorder\IncomingEntry
     */
    protected $entry;

    /**
     * Create a new job instance.
     */
    public function __construct(IncomingEntry $entry)
    {
        $this->entry = $entry;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EntryRepositoryInterface $repository)
    {
        $repository->create($this->entry);

        event(new RequestRecorded($this->entry));
    }
}
