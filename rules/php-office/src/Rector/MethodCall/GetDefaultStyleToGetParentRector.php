<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#dedicated-class-to-manipulate-coordinates
 */
final class GetDefaultStyleToGetParentRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Methods to (new Worksheet())->getDefaultStyle() to getParent()->getDefaultStyle()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->getDefaultStyle();
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->getParent()->getDefaultStyle();
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return class-string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node->var, 'PHPExcel_Worksheet')) {
            return null;
        }

        if (! $this->isName($node->name, 'getDefaultStyle')) {
            return null;
        }

        $variable = clone $node->var;

        $getParentMethodCall = new MethodCall($variable, 'getParent');
        $node->var = $getParentMethodCall;

        return $node;
    }
}
