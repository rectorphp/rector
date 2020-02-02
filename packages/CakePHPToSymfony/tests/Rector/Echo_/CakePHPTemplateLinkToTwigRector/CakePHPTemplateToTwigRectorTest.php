<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\Echo_\CakePHPTemplateLinkToTwigRector;

use Iterator;
use Rector\CakePHPToSymfony\Rector\Echo_\CakePHPTemplateLinkToTwigRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPTemplateToTwigRectorTest extends AbstractRectorTestCase
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
        return CakePHPTemplateLinkToTwigRector::class;
    }
}
