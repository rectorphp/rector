<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadDocBlock\DeadReturnTagValueNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\RemoveUselessReturnTagRectorTest
 */
final class RemoveUselessReturnTagRector extends AbstractRector
{
    /**
     * @var DeadReturnTagValueNodeAnalyzer
     */
    private $deadReturnTagValueNodeAnalyzer;

    public function __construct(DeadReturnTagValueNodeAnalyzer $deadReturnTagValueNodeAnalyzer)
    {
        $this->deadReturnTagValueNodeAnalyzer = $deadReturnTagValueNodeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove @return docblock with same type as defined in PHP',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use stdClass;

class SomeClass
{
    /**
     * @return stdClass
     */
    public function foo(): stdClass
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use stdClass;

class SomeClass
{
    public function foo(): stdClass
    {
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        $attributeAwareReturnTagValueNode = $phpDocInfo->getReturnTagValue();
        if ($attributeAwareReturnTagValueNode === null) {
            return null;
        }

        if (! $this->deadReturnTagValueNodeAnalyzer->isDead($attributeAwareReturnTagValueNode, $node)) {
            return null;
        }

        $phpDocInfo->removeTagValueNodeFromNode($attributeAwareReturnTagValueNode);

        return $node;
    }
}
