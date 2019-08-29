<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector;

use Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDelegatingParentCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            // skip
            // see https://3v4l.org/Plbu5
            __DIR__ . '/Fixture/skip_access_override.php.inc',
            __DIR__ . '/Fixture/skip_extra_arguments.php.inc',
            __DIR__ . '/Fixture/skip_extra_content.php.inc',
            __DIR__ . '/Fixture/skip_in_trait.php.inc',
            __DIR__ . '/Fixture/skip_different_method_name.php.inc',
            __DIR__ . '/Fixture/skip_changed_arguments.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveDelegatingParentCallRector::class;
    }
}
