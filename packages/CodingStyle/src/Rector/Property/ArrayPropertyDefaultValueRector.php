<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ArrayPropertyDefaultValueRector extends AbstractRector
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Array property should have default value, to prevent undefined array issues', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private $items;

    public function run()
    {
        foreach ($items as $item) {
        }
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private $items = [];

    public function run()
    {
        foreach ($items as $item) {
        }
    }
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
        if ($node->default) {
            return null;
        }

        /** @var Node\Stmt\Property $parentNode */
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        $varTypeInfo = $this->docBlockManipulator->getVarTypeInfo($parentNode);
        if ($varTypeInfo === null) {
            return null;
        }

        if (! $varTypeInfo->isIterable()) {
            return null;
        }

        $node->default = new Node\Expr\Array_();

        return $node;
    }
}
