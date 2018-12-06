<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\SensitiveDefineRector;

use Rector\Php\Rector\FuncCall\SensitiveDefineRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SensitiveDefineRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return SensitiveDefineRector::class;
    }
}
