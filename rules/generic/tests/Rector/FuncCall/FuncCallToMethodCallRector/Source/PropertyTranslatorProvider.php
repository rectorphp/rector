<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source;

abstract class PropertyTranslatorProvider
{
    /**
     * @var SomeTranslator
     */
    public $existingTranslator;
}
