<?php

namespace Rector\Tests\DeadCode\Rector\Cast\RecastingRemovalRector\Rector;

class AConfig
{
    private $data = [];

    public function __get($name)
    {
        if (isset($this->data[$name])) {
             return $this->data[$name];
        }
    }

    public function __set($name, $value)
    {
        $this->data[$name] = (object) $value;
    }
}

class SkipWithSetGetMagic
{
    public function run(AConfig $config)
    {
        $config->property = [];
        var_dump([] === (array) $config->property);
    }
}

?>
