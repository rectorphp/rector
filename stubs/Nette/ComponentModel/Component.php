<?php

declare(strict_types=1);

namespace Nette\ComponentModel;

if (class_exists('Nette\ComponentModel\Component')) {
    return;
}

class Component extends Container implements \ArrayAccess
{
    public function offsetExists($offset)
    {

    }

    public function offsetGet($offset)
    {

    }

    public function offsetSet($offset, $value)
    {

    }

    public function offsetUnset($offset)
    {

    }
}
