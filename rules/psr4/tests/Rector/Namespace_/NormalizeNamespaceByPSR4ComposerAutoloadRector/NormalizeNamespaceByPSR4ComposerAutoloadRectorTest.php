<?php

declare(strict_types=1);

namespace Rector\PSR4\Tests\Rector\Namespace_\NormalizeNamespaceByPSR4ComposerAutoloadRector;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\PSR4\Rector\Namespace_\NormalizeNamespaceByPSR4ComposerAutoloadRector
 */
final class NormalizeNamespaceByPSR4ComposerAutoloadRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/normalize_file.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);
    }

    protected function provideConfigFileInfo(): SmartFileInfo
    {
        return new SmartFileInfo(__DIR__ . '/config/normalize_namespace_config.php');
    }
}
