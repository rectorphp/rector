<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\ClassMethod\ChangeSetIdTypeToUuidRector;

use Iterator;
use Rector\Doctrine\Rector\ClassMethod\ChangeSetIdTypeToUuidRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeSetIdTypeToUuidRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/set_id.inc.php'];
        yield [__DIR__ . '/Fixture/skip_setted_id.inc.php'];
    }

    protected function getRectorClass(): string
    {
        return ChangeSetIdTypeToUuidRector::class;
    }
}
