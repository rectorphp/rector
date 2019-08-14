<?php declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\FuncCall\ArrayHelperToIlluminateSupportClassRector;

use Rector\Laravel\Rector\FuncCall\ArrayHelperToIlluminateSupportClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ArrayHelperToIlluminateSupportClassRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ArrayHelperToIlluminateSupportClassRector::class;
    }
}
