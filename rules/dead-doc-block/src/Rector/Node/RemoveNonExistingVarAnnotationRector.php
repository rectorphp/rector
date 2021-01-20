<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\Rector\Node;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\While_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Symplify\PackageBuilder\Php\TypeChecker;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadDocBlock\Tests\Rector\Node\RemoveNonExistingVarAnnotationRector\RemoveNonExistingVarAnnotationRectorTest
 *
 * @see https://github.com/phpstan/phpstan/commit/d17e459fd9b45129c5deafe12bca56f30ea5ee99#diff-9f3541876405623b0d18631259763dc1
 */
final class RemoveNonExistingVarAnnotationRector extends AbstractRector
{
    /**
     * @var class-string[]
     */
    private const NODES_TO_MATCH = [
        Assign::class,
        AssignRef::class,
        Foreach_::class,
        Static_::class,
        Echo_::class,
        Return_::class,
        Expression::class,
        Throw_::class,
        If_::class,
        While_::class,
        Switch_::class,
        Nop::class,
    ];

    /**
     * @var TypeChecker
     */
    private $typeChecker;

    public function __construct(TypeChecker $typeChecker)
    {
        $this->typeChecker = $typeChecker;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Removes non-existing @var annotations above the code',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function get()
    {
        /** @var Training[] $trainings */
        return $this->getData();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function get()
    {
        return $this->getData();
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
        return [Node::class];
    }

    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (! $varTagValueNode instanceof VarTagValueNode) {
            return null;
        }

        $variableName = ltrim($varTagValueNode->variableName, '$');
        if ($this->hasVariableName($node, $variableName)) {
            return null;
        }

        $comments = $node->getComments();
        if (isset($comments[1]) && $comments[1] instanceof Comment) {
            $this->rollbackComments($node, $comments[1]);
            return $node;
        }

        $phpDocInfo->removeByType(VarTagValueNode::class);
        return $node;
    }

    private function shouldSkip(Node $node): bool
    {
        if ($node instanceof Nop && count($node->getComments()) > 1) {
            return true;
        }

        return ! $this->typeChecker->isInstanceOf($node, self::NODES_TO_MATCH);
    }

    private function hasVariableName(Node $node, string $variableName): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($node, function (Node $node) use ($variableName): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            return $this->isName($node, $variableName);
        });
    }
}
