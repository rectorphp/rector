<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector;

use Rector\Symfony\Rector\Class_\MakeCommandLazyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MakeCommandLazyRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/in_construct.php.inc',
            __DIR__ . '/Fixture/in_construct_with_param.php.inc',
            __DIR__ . '/Fixture/constant_defined_name.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return MakeCommandLazyRector::class;
    }
}
