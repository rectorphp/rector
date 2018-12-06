<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Validator\ConstraintUrlOptionRector;

use Rector\Symfony\Rector\Validator\ConstraintUrlOptionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConstraintUrlOptionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return ConstraintUrlOptionRector::class;
    }
}
