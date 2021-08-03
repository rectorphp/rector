<?php

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Source\MessageInterface;

final class SkipInterface
{
    public function run(MessageInterface $message)
    {
        return $message->withStatus(500);
    }
}
