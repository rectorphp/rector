<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector;

use Rector\NetteKdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceEventManagerWithEventSubscriberRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/fixture.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);
        $this->doTestExtraFile('Event/SomeClassCopyEvent.php', __DIR__ . '/Source/ExpectedSomeClassCopyEvent.php');
    }

    protected function getRectorClass(): string
    {
        return ReplaceEventManagerWithEventSubscriberRector::class;
    }
}
