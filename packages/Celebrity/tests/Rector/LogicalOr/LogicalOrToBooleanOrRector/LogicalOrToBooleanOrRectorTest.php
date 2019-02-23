<?php declare(strict_types=1);

namespace Rector\Celebrity\Tests\Rector\LogicalOr\LogicalOrToBooleanOrRector;

use Rector\Celebrity\Rector\LogicalOr\LogicalOrToBooleanOrRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class LogicalOrToBooleanOrRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return LogicalOrToBooleanOrRector::class;
    }
}
