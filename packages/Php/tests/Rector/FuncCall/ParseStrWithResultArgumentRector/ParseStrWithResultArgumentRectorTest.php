<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\ParseStrWithResultArgumentRector;

use Rector\Php\Rector\FuncCall\ParseStrWithResultArgumentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ParseStrWithResultArgumentRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ParseStrWithResultArgumentRector::class;
    }
}
