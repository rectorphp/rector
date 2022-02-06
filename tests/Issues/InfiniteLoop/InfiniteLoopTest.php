<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\InfiniteLoop;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InfiniteLoopTest extends AbstractRectorTestCase
{
    public function testException(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/some_method_call_infinity.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);
    }

    public function testPass(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/de_morgan.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/infinite_loop.php';
    }
}
