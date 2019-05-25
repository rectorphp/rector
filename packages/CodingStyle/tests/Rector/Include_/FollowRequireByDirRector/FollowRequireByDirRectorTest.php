<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Include_\FollowRequireByDirRector;

use Rector\CodingStyle\Rector\Include_\FollowRequireByDirRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FollowRequireByDirRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/extra_dot.php.inc',
            __DIR__ . '/Fixture/phar.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return FollowRequireByDirRector::class;
    }
}
