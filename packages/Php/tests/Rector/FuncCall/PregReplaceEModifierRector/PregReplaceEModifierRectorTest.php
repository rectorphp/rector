<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\PregReplaceEModifierRector;

use Rector\Php\Rector\FuncCall\PregReplaceEModifierRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PregReplaceEModifierRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/call_function.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return PregReplaceEModifierRector::class;
    }
}
