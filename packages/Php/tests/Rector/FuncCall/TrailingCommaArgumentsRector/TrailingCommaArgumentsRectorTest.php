<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\TrailingCommaArgumentsRector;

use Rector\Php\Rector\FuncCall\TrailingCommaArgumentsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TrailingCommaArgumentsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return TrailingCommaArgumentsRector::class;
    }
}
