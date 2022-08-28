<?php

namespace Sinemah\CouchEloquent\Query\Parser\Traits;

use ReflectionException;
use ReflectionProperty;

trait HasAttributes
{
    protected function loadAttributes(array $values): void
    {
        foreach ($values as $name => $value) {
            if($property = $this->getProperty($this, $name)) {
                if($property->isPublic()) {
                    $this->$name = $value;
                }
            }
        }
    }

    protected function getProperty(mixed $class, $name): ?ReflectionProperty
    {
        $property = null;

        try {
            $property = new ReflectionProperty(get_class($class), $name);
        }catch(ReflectionException $e) {
        }

        return $property;
    }
}
