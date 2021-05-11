<?php

declare(strict_types=1);

namespace Rector\Tests\ChangesReporting\Annotation;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\ChangesReporting\Annotation\AnnotationExtractor;
use Rector\Tests\ChangesReporting\Annotation\AppliedRectorsChangelogResolver\Source\RectorWithChangelog;
use Rector\Tests\ChangesReporting\Annotation\AppliedRectorsChangelogResolver\Source\RectorWithOutChangelog;

final class AnnotationExtractorTest extends TestCase
{
    private AnnotationExtractor $annotationExtractor;

    protected function setUp(): void
    {
        $this->annotationExtractor = new AnnotationExtractor();
    }

    /**
     * @dataProvider extractAnnotationProvider()
     */
    public function testExtractAnnotationFromClass(string $className, string $annotation, ?string $expected): void
    {
        $value = $this->annotationExtractor->extractAnnotationFromClass($className, $annotation);
        $this->assertSame($expected, $value);
    }

    public function extractAnnotationProvider(): Iterator
    {
        yield 'Class with changelog annotation' => [
            RectorWithChangelog::class,
            '@changelog',
            'https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md',
        ];

        yield 'Class without changelog annotation' => [RectorWithOutChangelog::class, '@changelog', null];
    }
}
