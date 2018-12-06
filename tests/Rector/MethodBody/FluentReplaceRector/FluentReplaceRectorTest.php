<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodBody\FluentReplaceRector;

use Rector\Rector\MethodBody\FluentReplaceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodBody\FluentReplaceRector\Source\FluentInterfaceClass;

final class FluentReplaceRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return FluentReplaceRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [FluentInterfaceClass::class];
    }
}
