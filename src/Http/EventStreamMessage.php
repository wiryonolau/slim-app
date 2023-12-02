<?php

declare(strict_types=1);

namespace Itseasy\Http;

class EventStreamMessage
{
    protected $event;
    protected $data;

    public function __construct(string $event = "default", array $data = [])
    {
        $this->event = $event;
        $this->data = $data;
    }

    public function hasData(): bool
    {
        return (!empty($data));
    }

    public function getMessage(): string
    {
        return sprintf(
            "event: %s\ndata: %s\n\n ",
            $this->event,
            json_encode($this->data)
        );
    }
}
