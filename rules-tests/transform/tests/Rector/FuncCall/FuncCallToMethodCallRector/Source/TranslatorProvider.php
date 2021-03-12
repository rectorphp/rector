<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source;

abstract class TranslatorProvider
{
    private $translator;

    public function getTranslator(): SomeTranslator
    {
        return $this->translator;
    }
}
