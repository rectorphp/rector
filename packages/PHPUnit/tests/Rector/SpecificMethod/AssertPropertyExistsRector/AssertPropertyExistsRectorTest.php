<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertPropertyExistsRector;

use Rector\PHPUnit\Rector\SpecificMethod\AssertPropertyExistsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertPropertyExistsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    public function getRectorClass(): string
    {
        return AssertPropertyExistsRector::class;
    }
}
