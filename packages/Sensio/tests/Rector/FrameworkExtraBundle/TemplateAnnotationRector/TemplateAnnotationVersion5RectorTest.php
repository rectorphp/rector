<?php

declare(strict_types=1);

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector;

use Iterator;
use Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TemplateAnnotationVersion5RectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/Version5/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/Version5/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/Version5/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/Version5/fixture4.php.inc'];
        yield [__DIR__ . '/Fixture/Version5/fixture5.php.inc'];
        yield [__DIR__ . '/Fixture/Version5/with_route_too.php.inc'];
        yield [__DIR__ . '/Fixture/Version5/with_route_options.php.inc'];
        yield [__DIR__ . '/Fixture/Version5/without_base_class.php.inc'];
        yield [__DIR__ . '/Fixture/Version5/skip_without_template.php.inc'];
        yield [__DIR__ . '/Fixture/Version5/with_return_types.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            TemplateAnnotationRector::class => [
                '$version' => 5,
            ],
        ];
    }
}
