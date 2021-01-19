<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\MethodCall\VariableMethodCallToServiceCallRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\MethodCall\VariableMethodCallToServiceCallRector;
use Rector\Transform\ValueObject\VariableMethodCallToServiceCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class VariableMethodCallToServiceCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            VariableMethodCallToServiceCallRector::class => [
                VariableMethodCallToServiceCallRector::VARIABLE_METHOD_CALLS_TO_SERVICE_CALLS => [
                    new VariableMethodCallToServiceCall(
                        'PhpParser\Node',
                        'getAttribute',
                        'php_doc_info',
                        'Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory',
                        'createFromNodeOrEmpty'
                    ),
                ],
            ],
        ];
    }
}
