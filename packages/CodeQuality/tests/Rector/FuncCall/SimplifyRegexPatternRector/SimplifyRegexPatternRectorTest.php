<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyRegexPatternRector;

use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyRegexPatternRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/pattern_in_constant.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return SimplifyRegexPatternRector::class;
    }
}
