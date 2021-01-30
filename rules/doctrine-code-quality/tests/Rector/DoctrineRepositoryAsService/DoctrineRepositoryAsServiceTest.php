<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Rector\DoctrineRepositoryAsService;

use Iterator;
use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Rector\Doctrine\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector;
use Rector\Doctrine\Rector\MethodCall\ServiceLocatorToDIRector;
use Rector\DoctrineCodeQuality\Rector\Class_\MoveRepositoryFromParentToConstructorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Doctrine\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector
 * @see \Rector\DoctrineCodeQuality\Rector\Class_\MoveRepositoryFromParentToConstructorRector
 * @see \Rector\Doctrine\Rector\MethodCall\ServiceLocatorToDIRector
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

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            # order matters, this needs to be first to correctly detect parent repository
            MoveRepositoryFromParentToConstructorRector::class => [],
            ServiceLocatorToDIRector::class => [],
            ReplaceParentRepositoryCallsByRepositoryPropertyRector::class => [],
            RemoveRepositoryFromEntityAnnotationRector::class => [],
        ];
    }
}
