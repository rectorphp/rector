<?php

declare(strict_types=1);

namespace Rector\Architecture\Tests\Rector\DoctrineRepositoryAsService;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
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
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yaml';
    }
}
