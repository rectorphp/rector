<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Source\SomeService as SignalSlotDispatcher;

class SlotReplacement
{
    /**
     * @var SignalSlotDispatcher
     */
    protected $signalSlotDispatcher;

    public function __construct(SignalSlotDispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }
}
