<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\NotEqual\CommonNotEqualRector;

use Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CommonNotEqualRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return CommonNotEqualRector::class;
    }
}
