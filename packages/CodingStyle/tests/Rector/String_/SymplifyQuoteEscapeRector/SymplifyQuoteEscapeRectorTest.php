<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\String_\SymplifyQuoteEscapeRector;

use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SymplifyQuoteEscapeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return SymplifyQuoteEscapeRector::class;
    }
}
