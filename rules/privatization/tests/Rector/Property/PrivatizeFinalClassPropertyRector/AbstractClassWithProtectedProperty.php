<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector;

abstract class AbstractClassWithProtectedProperty
{
    /**
     * @var int
     */
    protected $value = 1000;
}
