<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Rector\DoctrineRepositoryAsService;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Doctrine\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector
 * @see \Rector\DoctrineCodeQuality\Rector\Class_\MoveRepositoryFromParentToConstructorRector
 * @see \Rector\Doctrine\Rector\MethodCall\EntityRepositoryServiceLocatorToDIRector
 */
final class DoctrineRepositoryAsServiceTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
