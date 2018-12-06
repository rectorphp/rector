<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\SimpleArrayCallableToStringRector;

use Rector\CodingStyle\Rector\FuncCall\SimpleArrayCallableToStringRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimpleArrayCallableToStringRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return SimpleArrayCallableToStringRector::class;
    }
}
