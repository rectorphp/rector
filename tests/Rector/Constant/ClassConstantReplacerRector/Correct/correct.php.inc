<?php declare(strict_types=1);

namespace SomeNamespace;

use Rector\Tests\Rector\Constant\ClassConstantReplacerRector\Source\LocalFormEvents;

class SomeClass
{
    public function subscribe()
    {
        return [
            LocalFormEvents::PRE_SUBMIT,
            LocalFormEvents::PRE_SUBMIT
        ];
    }
}
