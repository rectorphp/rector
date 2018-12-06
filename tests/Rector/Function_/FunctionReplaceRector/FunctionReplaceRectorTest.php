<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Function_\FunctionReplaceRector;

use Rector\Rector\Function_\FunctionReplaceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FunctionReplaceRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return FunctionReplaceRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            'view' => 'Laravel\Templating\render',
            'sprintf' => 'Safe\sprintf',
        ];
    }
}
