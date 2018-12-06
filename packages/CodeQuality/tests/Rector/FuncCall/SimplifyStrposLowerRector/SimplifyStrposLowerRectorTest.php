<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyStrposLowerRector;

use Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyStrposLowerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return SimplifyStrposLowerRector::class;
    }
}
