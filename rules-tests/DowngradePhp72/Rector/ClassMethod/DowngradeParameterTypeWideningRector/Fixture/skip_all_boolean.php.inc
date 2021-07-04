<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface SomeInputInterface
{
    /**
     * @param bool $onlyParams
     */
    public function hasParameterOption($values, bool $onlyParams = false);
}

abstract class AbstractInput implements SomeInputInterface
{
}

final class SkipAllBoolean extends AbstractInput
{
    /**
     * {@inheritdoc}
     */
    public function hasParameterOption($values, bool $onlyParams = false)
    {
        return true;
    }
}

final class SomeArrayInput extends AbstractInput
{
    /**
     * @param bool $onlyParams
     */
    public function hasParameterOption($values, bool $onlyParams = false)
    {
        return true;
    }
}
