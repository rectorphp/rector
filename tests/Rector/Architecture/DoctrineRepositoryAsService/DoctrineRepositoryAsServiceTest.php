<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\Architecture\RepositoryAsService\ReplaceParentRepositoryCallsByRepositoryPropertyRector
 * @covers \Rector\Rector\Architecture\RepositoryAsService\MoveRepositoryFromParentToConstructorRector
 * @covers \Rector\Rector\Architecture\RepositoryAsService\ServiceLocatorToDIRector
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
