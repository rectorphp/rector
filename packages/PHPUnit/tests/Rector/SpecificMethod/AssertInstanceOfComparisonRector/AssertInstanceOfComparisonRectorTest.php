<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertInstanceOfComparisonRector;

use Rector\PHPUnit\Rector\SpecificMethod\AssertInstanceOfComparisonRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertInstanceOfComparisonRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return AssertInstanceOfComparisonRector::class;
    }
}
