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

    protected function getRectorClass(): string
    {
        return SwapFuncCallArgumentsRector::class;
    }

    /**
     * @return mixed[]|null
     */
    protected function getRectorConfiguration(): ?array
    {
        return [
            'some_function' => [1, 0],
        ];
    }
}
