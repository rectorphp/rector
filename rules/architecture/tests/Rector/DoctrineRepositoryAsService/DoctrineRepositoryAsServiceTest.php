<?php

declare(strict_types=1);

namespace Rector\Architecture\Tests\Rector\DoctrineRepositoryAsService;

use Iterator;
use Rector\Architecture\Rector\Class_\MoveRepositoryFromParentToConstructorRector;
use Rector\Architecture\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector;
use Rector\Architecture\Rector\MethodCall\ServiceLocatorToDIRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Architecture\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector
 * @see \Rector\Architecture\Rector\Class_\MoveRepositoryFromParentToConstructorRector
 * @see \Rector\Architecture\Rector\MethodCall\ServiceLocatorToDIRector
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
