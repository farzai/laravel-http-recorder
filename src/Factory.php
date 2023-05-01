<?php

namespace Farzai\HttpRecorder;

use Illuminate\Support\Manager;

class Factory extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('http-recorder.driver', 'database');
    }

    /**
     * Create a new database driver instance.
     *
     * @return \Farzai\HttpRecorder\Storage\DatabaseEntryRepository
     */
    protected function createDatabaseDriver()
    {
        $config = $this->config->get('http-recorder.drivers.database', []);

        return new Storage\DatabaseEntryRepository($config);
    }
}
