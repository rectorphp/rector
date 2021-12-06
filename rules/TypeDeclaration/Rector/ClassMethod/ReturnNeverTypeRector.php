<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PHPStan\Type\NeverType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeNestingScope\ValueObject\ControlStructure;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/noreturn_type
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\ReturnNeverTypeRectorTest
 */
final class ReturnNeverTypeRector extends AbstractRector
{
    public function __construct(
        private readonly ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard,
        private readonly PhpDocTypeChanger $phpDocTypeChanger
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add "never" return-type for methods that never return anything', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        throw new InvalidException();
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @return never
     */
    public function run()
    {
        throw new InvalidException();
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::NEVER_TYPE)) {
            // never-type supported natively
            $node->returnType = new Name('never');
        } else {
            // static anlysis based never type
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
            $this->phpDocTypeChanger->changeReturnType($phpDocInfo, new NeverType());
        }

        return $node;
    }

    private function shouldSkip(ClassMethod | Function_ $node): bool
    {
        $return = $this->betterNodeFinder->findFirstInstanceOf($node, Return_::class);
        if ($return instanceof Return_) {
            return true;
        }

        $yieldAndConditionalNodes = array_merge([Yield_::class], ControlStructure::CONDITIONAL_NODE_SCOPE_TYPES);
        $hasNotNeverNodes = $this->hasNotNeverNodes($node, $yieldAndConditionalNodes);

        if ($hasNotNeverNodes) {
            return true;
        }

        $hasNeverNodes = $this->hasNeverNodes($node);
        $hasNeverFuncCall = $this->hasNeverFuncCall($node);

        if (! $hasNeverNodes && ! $hasNeverFuncCall) {
            return true;
        }

        if ($node instanceof ClassMethod && ! $this->parentClassMethodTypeOverrideGuard->isReturnTypeChangeAllowed(
            $node
        )) {
            return true;
        }

        if (! $node->returnType instanceof Node) {
            return false;
        }

        return $this->isName($node->returnType, 'never');
    }

    /**
     * @param class-string<Node>[] $yieldAndConditionalNodes
     */
    private function hasNotNeverNodes(ClassMethod | Function_ $functionLike, array $yieldAndConditionalNodes): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $functionLike->stmts, function (Node $subNode) use (
            $functionLike,
            $yieldAndConditionalNodes
        ): bool {
            if (! in_array($subNode::class, $yieldAndConditionalNodes, true)) {
                return false;
            }

            $parentFunctionOrClassMethod = $this->betterNodeFinder->findParentByTypes($subNode, $this->getNodeTypes());
            return $parentFunctionOrClassMethod === $functionLike;
        });
    }

    private function hasNeverNodes(ClassMethod | Function_ $functionLike): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $functionLike->stmts, function (Node $subNode) use (
            $functionLike
        ): bool {
            if (! in_array($subNode::class, [Node\Expr\Throw_::class, Throw_::class], true)) {
                return false;
            }

            $parentFunctionOrClassMethod = $this->betterNodeFinder->findParentByTypes($subNode, $this->getNodeTypes());
            return $parentFunctionOrClassMethod === $functionLike;
        });
    }

    private function hasNeverFuncCall(ClassMethod | Function_ $functionLike): bool
    {
        $hasNeverType = false;

        foreach ((array) $functionLike->stmts as $stmt) {
            if ($stmt instanceof Expression) {
                $stmt = $stmt->expr;
            }

            if ($stmt instanceof Stmt) {
                continue;
            }

            $stmtType = $this->getType($stmt);
            if ($stmtType instanceof NeverType) {
                $hasNeverType = true;
            }
        }

        return $hasNeverType;
    }
}
