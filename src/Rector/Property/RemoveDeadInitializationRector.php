<?php declare(strict_types=1);

namespace Rector\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveDeadInitializationRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove initialization with null value from property declarations', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SunshineCommand extends ParentClassWithNewConstructor
{
    private $myVar = null;
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SunshineCommand extends ParentClassWithNewConstructor
{
    private $myVar;
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [PropertyProperty::class];
    }

    /**
     * @param PropertyProperty $node
     */
    public function refactor(Node $node): ?Node
    {
        $defaultValueNode = $node->default;
        if ($defaultValueNode === null) {
            return null;
        }

        if (!($defaultValueNode instanceof ConstFetch)) {
            return null;
        }

        if (\strtolower((string) $defaultValueNode->name) !== 'null') {
            return null;
        }

        $node->default = null;

        return $node;
    }
}
