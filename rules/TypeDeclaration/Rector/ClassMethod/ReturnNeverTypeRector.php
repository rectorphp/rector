<?php

declare (strict_types=1);
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
final class ReturnNeverTypeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Defluent\ConflictGuard\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    public function __construct(\Rector\Defluent\ConflictGuard\ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add "never" return-type for methods that never return anything', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        throw new InvalidException();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::NEVER_TYPE)) {
            // never-type supported natively
            $node->returnType = new \PhpParser\Node\Name('never');
        } else {
            // static anlysis based never type
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
            $this->phpDocTypeChanger->changeReturnType($phpDocInfo, new \PHPStan\Type\NeverType());
        }
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function shouldSkip($node) : bool
    {
        $returns = $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Stmt\Return_::class);
        if ($returns !== []) {
            return \true;
        }
        $notNeverNodes = $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Expr\Yield_::class);
        if ($notNeverNodes !== []) {
            return \true;
        }
        $neverNodes = $this->betterNodeFinder->findInstancesOf($node, [\PhpParser\Node\Expr\Throw_::class, \PhpParser\Node\Stmt\Throw_::class]);
        $hasNeverFuncCall = $this->hasNeverFuncCall($node);
        if ($neverNodes === [] && !$hasNeverFuncCall) {
            return \true;
        }
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod && !$this->parentClassMethodTypeOverrideGuard->isReturnTypeChangeAllowed($node)) {
            return \true;
        }
        return $node->returnType && $this->isName($node->returnType, 'never');
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function hasNeverFuncCall($functionLike) : bool
    {
        $hasNeverType = \false;
        foreach ((array) $functionLike->stmts as $stmt) {
            if ($stmt instanceof \PhpParser\Node\Stmt\Expression) {
                $stmt = $stmt->expr;
            }
            if ($stmt instanceof \PhpParser\Node\Stmt) {
                continue;
            }
            $stmtType = $this->getType($stmt);
            if ($stmtType instanceof \PHPStan\Type\NeverType) {
                $hasNeverType = \true;
            }
        }
        return $hasNeverType;
    }
}
