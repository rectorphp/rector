<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\TestListenerToHooksRector;

use Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TestListenerToHooksRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/clear_it_all.php.inc',
            __DIR__ . '/Fixture/before_list_hook.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return TestListenerToHooksRector::class;
    }
}
