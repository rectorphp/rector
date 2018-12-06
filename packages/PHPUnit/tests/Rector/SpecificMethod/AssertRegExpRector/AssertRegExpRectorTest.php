<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertRegExpRector;

use Rector\PHPUnit\Rector\SpecificMethod\AssertRegExpRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertRegExpRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return AssertRegExpRector::class;
    }
}
