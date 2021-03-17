<?php

declare(strict_types=1);

namespace Rector\Tests\Symfony5\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector;

use Iterator;
use Rector\Symfony5\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DefinitionAliasSetPrivateToSetPublicRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return DefinitionAliasSetPrivateToSetPublicRector::class;
    }
}
