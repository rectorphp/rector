<?php

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Source\UsesTrait;

final class SkipCloneObjectAssignUseTrait
{
    public function run(UsesTrait $usesTrait)
    {
        return $usesTrait->withStatus(500);
    }
}
