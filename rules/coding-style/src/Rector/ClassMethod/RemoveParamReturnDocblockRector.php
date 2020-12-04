<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadDocBlock\DeadParamTagValueNodeAnalyzer;
use Rector\DeadDocBlock\DeadReturnTagValueNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\RemoveParamReturnDocblockRectorTest
 */
final class RemoveParamReturnDocblockRector extends AbstractRector
{
    /**
     * @var DeadParamTagValueNodeAnalyzer
     */
    private $deadParamTagValueNodeAnalyzer;

    /**
     * @var DeadReturnTagValueNodeAnalyzer
     */
    private $deadReturnTagValueNodeAnalyzer;

    public function __construct(
        DeadParamTagValueNodeAnalyzer $deadParamTagValueNodeAnalyzer,
        DeadReturnTagValueNodeAnalyzer $deadReturnTagValueNodeAnalyzer
    ) {
        $this->deadParamTagValueNodeAnalyzer = $deadParamTagValueNodeAnalyzer;
        $this->deadReturnTagValueNodeAnalyzer = $deadReturnTagValueNodeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove @param and @return docblock with same type and no description on typed argument and return',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use stdClass;

class SomeClass
{
    /**
     * @param string $a
     * @param string $b description
     * @return stdClass
     */
    function foo(string $a, string $b): stdClass
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use stdClass;

class SomeClass
{
    /**
     * @param string $b description
     */
    function foo(string $a, string $b): stdClass
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

        $this->refactorParamTags($node, $phpDocInfo);
        $this->refactorReturnTag($node, $phpDocInfo);

        return $node;
    }

    private function refactorParamTags(ClassMethod $classMethod, PhpDocInfo $phpDocInfo): void
    {
        foreach ($phpDocInfo->getParamTagValueNodes() as $paramTagValueNode) {
            if (! $this->deadParamTagValueNodeAnalyzer->isDead($paramTagValueNode, $classMethod)) {
                continue;
            }

            $phpDocInfo->removeTagValueNodeFromNode($paramTagValueNode);
        }
    }

    private function refactorReturnTag(ClassMethod $classMethod, PhpDocInfo $phpDocInfo): void
    {
        $attributeAwareReturnTagValueNode = $phpDocInfo->getReturnTagValue();
        if ($attributeAwareReturnTagValueNode === null) {
            return;
        }

        if (! $this->deadReturnTagValueNodeAnalyzer->isDead($attributeAwareReturnTagValueNode, $classMethod)) {
            return;
        }

        $phpDocInfo->removeTagValueNodeFromNode($attributeAwareReturnTagValueNode);
    }
}
