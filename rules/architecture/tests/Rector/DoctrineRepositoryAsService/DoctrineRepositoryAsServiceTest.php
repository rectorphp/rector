<?php

declare(strict_types=1);

namespace Rector\Architecture\Tests\Rector\DoctrineRepositoryAsService;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Architecture\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector
 * @covers \Rector\Architecture\Rector\Class_\MoveRepositoryFromParentToConstructorRector
 * @covers \Rector\Architecture\Rector\MethodCall\ServiceLocatorToDIRector
 */
final class DoctrineRepositoryAsServiceTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
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
