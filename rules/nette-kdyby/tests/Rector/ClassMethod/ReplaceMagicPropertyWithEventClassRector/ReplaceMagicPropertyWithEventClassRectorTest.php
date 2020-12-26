<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector;

use Rector\NetteKdyby\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceMagicPropertyWithEventClassRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/fixture.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);
    }

    protected function getRectorClass(): string
    {
        return ReplaceMagicPropertyWithEventClassRector::class;
    }
}
