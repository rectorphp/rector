<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\SwapFuncCallArgumentsRector;

use Rector\Php\Rector\FuncCall\SwapFuncCallArgumentsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SwapFuncCallArgumentsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            SwapFuncCallArgumentsRector::class => [
                '$newArgumentPositionsByFunctionName' => [
                    'some_function' => [1, 0],
                ],
            ],
        ];
    }
}
