<?php

namespace Vajexal\AmpSQLite\Tests;

use ReflectionObject;

function getPrivateProperty(object $object, string $property)
{
    $reflector = new ReflectionObject($object);
    $property = $reflector->getProperty($property);
    $property->setAccessible(true);
    return $property->getValue($object);
}
