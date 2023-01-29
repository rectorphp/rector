<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ConstantType;
use PHPStan\Type\GeneralizePrecision;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\ValueObject\TernaryIfElseTypes;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\ReturnTypeFromStrictTernaryRector\ReturnTypeFromStrictTernaryRectorTest
 */
final class ReturnTypeFromStrictTernaryRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add method return type based on strict ternary values', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function getValue($number)
    {
        return $number ? 100 : 500;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getValue($number): int
    {
        return $number ? 100 : 500;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->returnType instanceof Node) {
                continue;
            }
            $onlyStmt = $classMethod->stmts[0] ?? null;
            if (!$onlyStmt instanceof Return_) {
                continue;
            }
            if (!$onlyStmt->expr instanceof Ternary) {
                continue;
            }
            $ternary = $onlyStmt->expr;
            // has scalar in if/else of ternary
            $ternaryIfElseTypes = $this->matchScalarTernaryIfElseTypes($ternary);
            if (!$ternaryIfElseTypes instanceof TernaryIfElseTypes) {
                continue;
            }
            $ifType = $ternaryIfElseTypes->getFirstType();
            $elseType = $ternaryIfElseTypes->getSecondType();
            if (!$this->areTypesEqual($ifType, $elseType)) {
                continue;
            }
            if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod)) {
                continue;
            }
            $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($ifType, TypeKind::RETURN);
            if (!$returnTypeNode instanceof Node) {
                continue;
            }
            $classMethod->returnType = $returnTypeNode;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    private function isAlwaysScalarExpr(?Expr $expr) : bool
    {
        // check if Scalar node
        if ($expr instanceof Scalar) {
            return \true;
        }
        // check if constant
        if ($expr instanceof ConstFetch) {
            return \true;
        }
        // check if class constant
        return $expr instanceof ClassConstFetch;
    }
    private function areTypesEqual(Type $firstType, Type $secondType) : bool
    {
        // this is needed to make comparison tolerant to constant values, e.g. 5 and 10 are same only then
        if ($firstType instanceof ConstantType) {
            $firstType = $firstType->generalize(GeneralizePrecision::lessSpecific());
        }
        if ($secondType instanceof ConstantType) {
            $secondType = $secondType->generalize(GeneralizePrecision::lessSpecific());
        }
        return $firstType->equals($secondType);
    }
    private function matchScalarTernaryIfElseTypes(Ternary $ternary) : ?TernaryIfElseTypes
    {
        if (!$this->isAlwaysScalarExpr($ternary->if)) {
            return null;
        }
        if (!$this->isAlwaysScalarExpr($ternary->else)) {
            return null;
        }
        /** @var Node\Expr $if */
        $if = $ternary->if;
        /** @var Node\Expr $else */
        $else = $ternary->else;
        $ifType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($if);
        $elseType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($else);
        return new TernaryIfElseTypes($ifType, $elseType);
    }
}
