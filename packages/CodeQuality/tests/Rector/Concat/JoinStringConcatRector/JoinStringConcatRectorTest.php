<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector;

use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class JoinStringConcatRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return JoinStringConcatRector::class;
    }
}
