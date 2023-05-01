<?php

namespace Farzai\HttpRecorder;

use JsonSerializable;

abstract class AbstractEntry implements JsonSerializable
{
    private array $data;

    /**
     * Create a new entry instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the array representation of the entry.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Get the JSON representation of the entry.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
}
