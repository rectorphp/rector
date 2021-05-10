<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\PhpDoc\DeadParamTagValueNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\RemoveUselessParamTagRectorTest
 */
final class RemoveUselessParamTagRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\DeadCode\PhpDoc\DeadParamTagValueNodeAnalyzer
     */
    private $deadParamTagValueNodeAnalyzer;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(\Rector\DeadCode\PhpDoc\DeadParamTagValueNodeAnalyzer $deadParamTagValueNodeAnalyzer, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover)
    {
        $this->deadParamTagValueNodeAnalyzer = $deadParamTagValueNodeAnalyzer;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove @param docblock with same type as parameter type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param string $a
     * @param string $b description
     */
    public function foo(string $a, string $b)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param string $b description
     */
    public function foo(string $a, string $b)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($phpDocInfo->getParamTagValueNodes() as $paramTagValueNode) {
            if (!$this->deadParamTagValueNodeAnalyzer->isDead($paramTagValueNode, $node)) {
                continue;
            }
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $paramTagValueNode);
        }
        if ($phpDocInfo->hasChanged()) {
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::HAS_PHP_DOC_INFO_JUST_CHANGED, \true);
            return $node;
        }
        return null;
    }
}
