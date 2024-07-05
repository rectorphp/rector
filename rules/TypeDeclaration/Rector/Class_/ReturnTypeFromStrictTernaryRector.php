<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\ReturnTypeFromStrictTernaryRector\ReturnTypeFromStrictTernaryRectorTest
 */
final class ReturnTypeFromStrictTernaryRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReturnTypeInferer $returnTypeInferer, BetterNodeFinder $betterNodeFinder, StaticTypeMapper $staticTypeMapper, ReturnAnalyzer $returnAnalyzer)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->returnAnalyzer = $returnAnalyzer;
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if ($this->shouldSkip($node, $scope)) {
            return null;
        }
        if ($node->stmts === null) {
            return null;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($node);
        if (\count($returns) !== 1) {
            return null;
        }
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returns)) {
            return null;
        }
        $return = $returns[0];
        if (!$return->expr instanceof Ternary) {
            return null;
        }
        $ternary = $return->expr;
        $returnScope = $return->expr->getAttribute(AttributeKey::SCOPE);
        if (!$returnScope instanceof Scope) {
            return null;
        }
        $nativeTernaryType = $returnScope->getNativeType($ternary);
        if ($nativeTernaryType instanceof MixedType) {
            return null;
        }
        $ternaryType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($ternary);
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($ternaryType, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Node) {
            return null;
        }
        $node->returnType = $returnTypeNode;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function shouldSkip($functionLike, Scope $scope) : bool
    {
        // type is already filled, skip
        if ($functionLike->returnType instanceof Node) {
            return \true;
        }
        $returnType = $this->returnTypeInferer->inferFunctionLike($functionLike);
        $returnType = TypeCombinator::removeNull($returnType);
        if ($returnType instanceof UnionType) {
            return \true;
        }
        return $functionLike instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($functionLike, $scope);
    }
}
