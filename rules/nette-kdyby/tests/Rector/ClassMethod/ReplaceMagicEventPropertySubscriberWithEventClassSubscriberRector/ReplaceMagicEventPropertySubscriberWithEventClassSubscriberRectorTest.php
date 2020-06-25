<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteKdyby\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/fixture.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);
    }

    protected function getRectorClass(): string
    {
        return ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector::class;
    }
}
