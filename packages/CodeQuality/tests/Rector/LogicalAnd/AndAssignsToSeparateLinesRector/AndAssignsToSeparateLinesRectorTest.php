<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\LogicalAnd\AndAssignsToSeparateLinesRector;

use Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AndAssignsToSeparateLinesRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/keep_in_condition.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return AndAssignsToSeparateLinesRector::class;
    }
}
