<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Function_\RenameFunctionRector;

use Rector\Rector\Function_\RenameFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameFunctionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/double_function.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RenameFunctionRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            'view' => 'Laravel\Templating\render',
            'sprintf' => 'Safe\sprintf',
            'hebrevc' => ['nl2br', 'hebrev'],
        ];
    }
}
