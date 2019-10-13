<?php

declare(strict_types=1);

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector;

use Iterator;
use Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TemplateAnnotationVersion3RectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/Version3/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/Version3/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/Version3/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/Version3/fixture4.php.inc'];
        yield [__DIR__ . '/Fixture/Version3/skip_just_template.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            TemplateAnnotationRector::class => [
                '$version' => 3,
            ],
        ];
    }
}
