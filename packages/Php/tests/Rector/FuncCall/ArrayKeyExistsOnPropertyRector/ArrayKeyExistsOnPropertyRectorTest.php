<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\ArrayKeyExistsOnPropertyRector;

use Rector\Php\Rector\FuncCall\ArrayKeyExistsOnPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ArrayKeyExistsOnPropertyRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ArrayKeyExistsOnPropertyRector::class;
    }
}
