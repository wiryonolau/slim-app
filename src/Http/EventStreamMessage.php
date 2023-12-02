<?php

declare(strict_types=1);

namespace Itseasy\Http;

use Exception;
use Psr\Http\Message\StreamInterface;

class EventStreamMessage
{
    protected $event;
    protected $data;

    public function __construct($data, ?string $event = null)
    {
        $this->event = $event ?: "default";

        if (method_exists($data, "getArrayCopy")) {
            $this->data = $data->getArrayCopy();
        } elseif (is_array($data)) {
            $this->data = $data;
        } else {
            throw new Exception("data must be array or implement getArrayCopy");
        }
    }

    public function hasData(): bool
    {
        return (!empty($this->data));
    }

    public function getMessage(): string
    {
        return sprintf(
            "event: %s\ndata: %s\n\n ",
            $this->event,
            json_encode($this->data)
        );
    }

    public function writeToStream(StreamInterface $stream)
    {
        if ($this->hasData()) {
            $stream->write($this->getMessage());
        }
    }
}
