<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\Echo_\CakePHPTemplateHToTwigRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPTemplateHToTwigRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return \Rector\CakePHPToSymfony\Rector\Echo_\CakePHPTemplateHToTwigRector::class;
    }
}
