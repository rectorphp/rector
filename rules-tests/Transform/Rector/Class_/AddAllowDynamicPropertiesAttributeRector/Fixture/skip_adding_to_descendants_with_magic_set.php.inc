<?php

namespace Rector\Tests\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector\Fixture;

class MagicSetObjects
{
    private array $props = [];

    public function __set(string $name, $value) {
        $this->props[$name] = $value;
    }

    public function __get(string $name) {
        return $this->props[$name];
    }
}

class AnotherMagicObject extends MagicSetObjects
{
    public string $data = 'hello world';
}

class YetAnotherMagic extends AnotherMagicObject
{
    public string $data = 'hello space';
}

?>
