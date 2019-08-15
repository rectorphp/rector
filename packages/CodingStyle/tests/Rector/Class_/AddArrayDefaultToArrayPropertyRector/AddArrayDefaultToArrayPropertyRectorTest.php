<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector;

use Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddArrayDefaultToArrayPropertyRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/count_on_null.php.inc',
            __DIR__ . '/Fixture/skip_nullable_array.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return AddArrayDefaultToArrayPropertyRector::class;
    }
}
