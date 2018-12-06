<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertNotOperatorRector;

use Rector\PHPUnit\Rector\SpecificMethod\AssertNotOperatorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertNotOperatorRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return AssertNotOperatorRector::class;
    }
}
