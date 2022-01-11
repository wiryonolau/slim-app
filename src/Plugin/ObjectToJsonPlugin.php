<?php

declare(strict_types=1);

namespace Itseasy\Plugin;

class ObjectToJsonPlugin implements PluginInterface
{
    public static $name = "objectToJson";

    public function getName() : string
    {
        return self::$name;
    }

    public function __invoke(
        $object,
        bool $as_json = true,
        int $flags = 0,
        int $depth = 512
    ) {
        $flags |= JSON_THROW_ON_ERROR;

        if (is_null($object)) {
            return "";
        }

        if (!$as_json) {
            return $object;
        }

        if (is_array($object)) {
            return json_encode($object, $flags, $depth);
        }

        if (method_exists($object, "getArrayCopy") and is_callable([$object, "getArrayCopy"])) {
            $value = $object->getArrayCopy();
            return json_encode($value);
        }

        throw new Exception("Object must be an array or at least has getArrayCopy function");
    }
}
