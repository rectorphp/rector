<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector\Fixture;

final class SkipPropertyPromotion
{
    public function __construct(private string $name)
    {
    }

    public function sayHello()
    {
        return "Hello {$this->name}";
    }
}

?>
