<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\String_\SplitStringClassConstantToClassConstFetchRector;

use Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SplitStringClassConstantToClassConstFetchRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return SplitStringClassConstantToClassConstFetchRector::class;
    }
}
