<?php

declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Source;

class Entity
{
}

final class SomeModelType
{
    public function makeEntity(): Entity
    {
    }
}
