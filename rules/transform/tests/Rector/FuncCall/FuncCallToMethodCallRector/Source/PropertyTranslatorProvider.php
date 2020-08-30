<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source;

abstract class PropertyTranslatorProvider
{
    /**
     * @var SomeTranslator
     */
    public $existingTranslator;
}
