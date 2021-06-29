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
use Rector\Defluent\ConflictGuard\ParentClassMethodTypeOverrideGuard;
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
        private ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard,
        private PhpDocTypeChanger $phpDocTypeChanger
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
        $returns = $this->betterNodeFinder->findInstanceOf($node, Return_::class);
        if ($returns !== []) {
            return true;
        }

        $notNeverNodes = $this->betterNodeFinder->findInstanceOf($node, Yield_::class);
        if ($notNeverNodes !== []) {
            return true;
        }

        $neverNodes = $this->betterNodeFinder->findInstancesOf($node, [Node\Expr\Throw_::class, Throw_::class]);

        $hasNeverFuncCall = $this->resolveHasNeverFuncCall($node);
        if ($neverNodes === [] && ! $hasNeverFuncCall) {
            return true;
        }

        if ($node instanceof ClassMethod && ! $this->parentClassMethodTypeOverrideGuard->isReturnTypeChangeAllowed(
            $node
        )) {
            return true;
        }

        return $node->returnType && $this->isName($node->returnType, 'never');
    }

    private function resolveHasNeverFuncCall(ClassMethod | Function_ $functionLike): bool
    {
        $hasNeverType = false;

        foreach ((array) $functionLike->stmts as $stmt) {
            if ($stmt instanceof Expression) {
                $stmt = $stmt->expr;
            }

            if ($stmt instanceof Stmt) {
                continue;
            }

            $stmtType = $this->getStaticType($stmt);
            if ($stmtType instanceof NeverType) {
                $hasNeverType = true;
            }
        }

        return $hasNeverType;
    }
}
