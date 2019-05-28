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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            FunctionToStaticCallRector::class => [
                '$functionToStaticCall' => [
                    'view' => ['SomeStaticClass', 'render'],
                    'SomeNamespaced\view' => ['AnotherStaticClass', 'render'],
                ],
            ],
        ];
    }
}
