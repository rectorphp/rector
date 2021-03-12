<?php

declare(strict_types=1);

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Source;

final class ClassWithCountableProperty
{
    /**
     * @var string[]
     */
    public $countable = ['test', 'test2'];
}
