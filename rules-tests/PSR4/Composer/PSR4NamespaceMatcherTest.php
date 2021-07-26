<?php

declare(strict_types=1);

namespace Rector\Tests\PSR4\Composer;

use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\ValueObject\Application\File;
use Rector\PSR4\Composer\PSR4AutoloadPathsProvider;
use Rector\PSR4\Composer\PSR4NamespaceMatcher;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PSR4NamespaceMatcherTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        $this->boot();
    }

    public function test(): void
    {
        $smartFileInfo = new SmartFileInfo(__DIR__ . '/Fixture-dashed/Config.php');
        $file = new File($smartFileInfo, $smartFileInfo->getContents());

        $psr4AutoloadPathsProvider = $this->getService(PSR4AutoloadPathsProvider::class);
        $psr4NamespaceMatcher = new PSR4NamespaceMatcher($psr4AutoloadPathsProvider);

        $this->assertNull($psr4NamespaceMatcher->getExpectedNamespace($file, new FileWithoutNamespace([])));
    }
}
