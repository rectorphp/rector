<?php
declare(strict_types=1);

namespace Rector\Tests\ChangesReporting\Annotation;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\ChangesReporting\Annotation\AnnotationExtractor;
use Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithChangelog;
use Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithOutChangelog;

final class AnnotationExtractorTest extends TestCase
{
    /**
     * @var AnnotationExtractor
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new AnnotationExtractor();
    }

    public function extractAnnotationProvider(): Iterator
    {
        $classWithChangeLog = new RectorWithChangelog();
        $classWithoutChangeLog = new RectorWithOutChangelog();

        yield 'Class with changelog annotation' => [
            get_class($classWithChangeLog),
            '@changelog',
            'https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md',
        ];

        yield 'Class without changelog annotation' => [get_class($classWithoutChangeLog), '@changelog', null];
    }

    /**
     * @dataProvider extractAnnotationProvider
     */
    public function testExtractAnnotationFromClass(string $className, string $annotation, ?string $expected): void
    {
        $value = $this->subject->extractAnnotationFromClass($className, $annotation);
        $this->assertSame($expected, $value);
    }
}
