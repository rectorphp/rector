<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipPropertyArrayFilledByConstruct
{
    /** @var array */
    private $property;

    public function __construct(array $property)
    {
        $this->property = $property;
    }

    public function run(): int
    {
        return count($this->property);
    }
}
