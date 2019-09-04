<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector;

use Rector\Php\Rector\FuncCall\CountOnNullRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CountOnNullRectorWithPHP73Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/is_countable.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return CountOnNullRector::class;
    }

    protected function getPhpVersion(): string
    {
        return '7.3';
    }
}
