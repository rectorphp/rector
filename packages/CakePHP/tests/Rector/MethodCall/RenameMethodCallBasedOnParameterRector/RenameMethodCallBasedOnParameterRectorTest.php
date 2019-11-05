<?php

declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;

use Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;
use Rector\CakePHP\Tests\Rector\MethodCall\RenameMethodCallBasedOnParameterRector\Source\SomeModelType;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameMethodCallBasedOnParameterRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameMethodCallBasedOnParameterRector::class => [
                '$methodNamesByTypes' => [
                    SomeModelType::class => [
                        'invalidNoOptions' => [],
                        'getParam' => [
                            'match_parameter' => 'paging',
                            'replace_with' => 'getAttribute',
                        ],
                        'withParam' => [
                            'match_parameter' => 'paging',
                            'replace_with' => 'withAttribute',
                        ],
                    ],
                ],
            ],
        ];
    }
}
