<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector\RemoveNonExistingVarAnnotationRectorTest
 *
 * @changelog https://github.com/phpstan/phpstan/commit/d17e459fd9b45129c5deafe12bca56f30ea5ee99#diff-9f3541876405623b0d18631259763dc1
 */
final class RemoveNonExistingVarAnnotationRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Removes non-existing @var annotations above the code', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function get()
    {
        /** @var Training[] $trainings */
        return $this->getData();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function get()
    {
        return $this->getData();
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
        return [Stmt::class];
    }
    /**
     * @param Stmt $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return null;
        }
        if ($this->isObjectShapePseudoType($varTagValueNode)) {
            return null;
        }
        $variableName = \ltrim($varTagValueNode->variableName, '$');
        if ($variableName === '' && $this->isAnnotatableReturn($node)) {
            return null;
        }
        if ($this->hasVariableName($node, $variableName)) {
            return null;
        }
        $comments = $node->getComments();
        if (isset($comments[1])) {
            // skip edge case with double comment, as impossible to resolve by PHPStan doc parser
            return null;
        }
        $phpDocInfo->removeByType(VarTagValueNode::class);
        return $node;
    }
    private function shouldSkip(Stmt $stmt) : bool
    {
        if ($stmt instanceof ClassConst || $stmt instanceof Property) {
            return \true;
        }
        return \count($stmt->getComments()) !== 1;
    }
    private function hasVariableName(Stmt $stmt, string $variableName) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmt, function (Node $node) use($variableName) : bool {
            if (!$node instanceof Variable) {
                return \false;
            }
            return $this->isName($node, $variableName);
        });
    }
    /**
     * This is a hack,
     * that waits on phpdoc-parser to get merged - https://github.com/phpstan/phpdoc-parser/pull/145
     */
    private function isObjectShapePseudoType(VarTagValueNode $varTagValueNode) : bool
    {
        if (!$varTagValueNode->type instanceof IdentifierTypeNode) {
            return \false;
        }
        if ($varTagValueNode->type->name !== 'object') {
            return \false;
        }
        if (\strncmp($varTagValueNode->description, '{', \strlen('{')) !== 0) {
            return \false;
        }
        return \strpos($varTagValueNode->description, '}') !== \false;
    }
    private function isAnnotatableReturn(Stmt $stmt) : bool
    {
        return $stmt instanceof Return_ && $stmt->expr instanceof CallLike && !$stmt->expr instanceof New_;
    }
}
