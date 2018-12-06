<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\Architecture\RepositoryAsService\ReplaceParentRepositoryCallsByRepositoryPropertyRector
 * @covers \Rector\Rector\Architecture\RepositoryAsService\MoveRepositoryFromParentToConstructorRector
 * @covers \Rector\Rector\Architecture\RepositoryAsService\ServiceLocatorToDIRector
 */
final class DoctrineRepositoryAsServiceTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/PostController.php']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
