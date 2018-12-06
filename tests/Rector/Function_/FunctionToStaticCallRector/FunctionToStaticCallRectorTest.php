<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Function_\FunctionToStaticCallRector;

use Rector\Rector\Function_\FunctionToStaticCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FunctionToStaticCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return FunctionToStaticCallRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            'view' => ['SomeStaticClass', 'render'],
            'SomeNamespaced\view' => ['AnotherStaticClass', 'render'],
        ];
    }
}
