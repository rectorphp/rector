<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Identical\IdenticalFalseToBooleanNotRector;

use Rector\CodingStyle\Rector\Identical\IdenticalFalseToBooleanNotRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IdenticalFalseToBooleanNotRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return IdenticalFalseToBooleanNotRector::class;
    }
}
