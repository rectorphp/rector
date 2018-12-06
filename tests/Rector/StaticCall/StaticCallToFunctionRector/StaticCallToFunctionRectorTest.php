<?php declare(strict_types=1);

namespace Rector\Tests\Rector\StaticCall\StaticCallToFunctionRector;

use Rector\Rector\StaticCall\StaticCallToFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\StaticCall\StaticCallToFunctionRector\Source\SomeOldStaticClass;

final class StaticCallToFunctionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return StaticCallToFunctionRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [SomeOldStaticClass::class => ['render' => 'view']];
    }
}
