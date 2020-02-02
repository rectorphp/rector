<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\Echo_\CakePHPTemplateTranslateToTwigRector;

use Iterator;
use Rector\CakePHPToSymfony\Rector\Echo_\CakePHPTemplateTranslateToTwigRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPTemplateTranslateToTwigRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFileWithoutAutoload($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return CakePHPTemplateTranslateToTwigRector::class;
    }
}
