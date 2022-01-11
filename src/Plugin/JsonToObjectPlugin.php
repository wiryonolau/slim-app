<?php

declare(strict_types=1);

namespace Itseasy\Plugin;

use Laminas\Stdlib\ArraySerializableInterface;

class JsonToObjectPlugin implements PluginInterface
{
    public static $name = "jsonToObject";

    public function getName() : string
    {
        return self::$name;
    }

    public function __invoke(
        $json,
        $objectPrototype = null,
        int $depth = 512,
        int $flags = 0
    ) {
        $flags |= JSON_THROW_ON_ERROR;

        if (is_array($json)) {
            return $json;
        }

        $json = json_decode($json, true, $depth, $flags);

        if (is_null($objectPrototype)) {
            return $json;
        }

        if (is_string($objectPrototype) and class_exists($objectPrototype)) {
            $objectPrototype = new $objectPrototype;
        }

        $obj = clone $objectPrototype;
        if (method_exists($objectPrototype, "populate") and is_callable([$objectPrototype, "populate"])) {
            $obj->populate($json);
            return $obj;
        }

        if ($objectPrototype instanceof ArraySerializableInterface) {
            $obj->exchangeArray($json);
            return $obj;
        }

        throw new Exception("objecProperty must implement ArraySerializableInterface or at least has populate function");
    }


}
