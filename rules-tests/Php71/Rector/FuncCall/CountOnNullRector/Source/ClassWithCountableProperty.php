<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Source;

final class ClassWithCountableProperty
{
    /**
     * @var string[]
     */
    public $countable = ['test', 'test2'];
}
