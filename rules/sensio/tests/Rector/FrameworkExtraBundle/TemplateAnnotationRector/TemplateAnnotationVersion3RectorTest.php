<?php

declare(strict_types=1);

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TemplateAnnotationVersion3RectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureVersion3');
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
