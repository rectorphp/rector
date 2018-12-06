<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Rector\MethodBody\NormalToFluentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

final class NormalToFluentRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return NormalToFluentRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [FluentInterfaceClass::class => ['someFunction', 'otherFunction', 'joinThisAsWell']];
    }
}
