<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Php\Rector\FuncCall\CountOnNullRector
 */
final class CountOnNullRectorWithPHP73Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/is_countable.php.inc']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/rector_with_php73.yaml';
    }
}
