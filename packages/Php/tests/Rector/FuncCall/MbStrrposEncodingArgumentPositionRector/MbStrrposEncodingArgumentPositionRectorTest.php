<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;

use Rector\Php\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MbStrrposEncodingArgumentPositionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return MbStrrposEncodingArgumentPositionRector::class;
    }
}
