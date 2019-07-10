<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector;

use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class EncapsedStringsToSprintfRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/numberz.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return EncapsedStringsToSprintfRector::class;
    }
}
