<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteKdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector;

final class ReplaceEventManagerWithEventSubscriberRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/fixture.php.inc');

        $expectedEventFilePath = $this->originalTempFileInfo->getPath() . '/Event/SomeClassCopyEvent.php';

        $this->assertFileExists($expectedEventFilePath);
        $this->assertFileEquals(__DIR__ . '/Source/ExpectedSomeClassCopyEvent.php', $expectedEventFilePath);
    }

    protected function getRectorClass(): string
    {
        return ReplaceEventManagerWithEventSubscriberRector::class;
    }
}
