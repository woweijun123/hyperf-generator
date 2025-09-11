<?php

namespace Riven\Exceptions;

use Exception;

class InvalidStructClass extends Exception
{
    public static function create(?string $class): InvalidStructClass
    {
        $message = $class === null
            ? 'Could not create a Struct object, no Struct class was given'
            : "Could not create a Struct object, `$class` does not implement `Struct`";

        return new self($message);
    }
}
