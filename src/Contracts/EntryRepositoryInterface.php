<?php

namespace Farzai\HttpRecorder\Contracts;

use Farzai\HttpRecorder\IncomingEntry;

interface EntryRepositoryInterface
{
    /**
     * Create a new entry.
     */
    public function create(IncomingEntry $entry);
}
