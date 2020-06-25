<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue2451;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Issue2451Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/fixture2451.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yaml';
    }
}
