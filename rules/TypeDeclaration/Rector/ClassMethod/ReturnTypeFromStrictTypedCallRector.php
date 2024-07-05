<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\Php\PhpVersionProvider;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\TypeDeclaration\NodeAnalyzer\TypeNodeUnwrapper;
use Rector\TypeDeclaration\TypeAnalyzer\ReturnStrictTypeAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\ReturnTypeFromStrictTypedCallRectorTest
 */
final class ReturnTypeFromStrictTypedCallRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\TypeNodeUnwrapper
     */
    private $typeNodeUnwrapper;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\ReturnStrictTypeAnalyzer
     */
    private $returnStrictTypeAnalyzer;
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
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    public function __construct(TypeNodeUnwrapper $typeNodeUnwrapper, ReturnStrictTypeAnalyzer $returnStrictTypeAnalyzer, ReturnTypeInferer $returnTypeInferer, BetterNodeFinder $betterNodeFinder, PhpVersionProvider $phpVersionProvider, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReturnAnalyzer $returnAnalyzer)
    {
        $this->typeNodeUnwrapper = $typeNodeUnwrapper;
        $this->returnStrictTypeAnalyzer = $returnStrictTypeAnalyzer;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return type from strict return type of call', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function getData()
    {
        return $this->getNumber();
    }

    private function getNumber(): int
    {
        return 1000;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getData(): int
    {
        return $this->getNumber();
    }

    private function getNumber(): int
    {
        return 1000;
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
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        // already filled → skip
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($node->stmts === null) {
            return null;
        }
        if ($this->shouldSkip($node, $scope)) {
            return null;
        }
        $currentScopeReturns = $this->betterNodeFinder->findReturnsScoped($node);
        $returnedStrictTypes = $this->returnStrictTypeAnalyzer->collectStrictReturnTypes($currentScopeReturns, $scope);
        if ($returnedStrictTypes === []) {
            return null;
        }
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $currentScopeReturns)) {
            return null;
        }
        if (\count($returnedStrictTypes) === 1) {
            return $this->refactorSingleReturnType($currentScopeReturns[0], $returnedStrictTypes[0], $node);
        }
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
            /** @var PhpParserUnionType[] $returnedStrictTypes */
            $unwrappedTypes = $this->typeNodeUnwrapper->unwrapNullableUnionTypes($returnedStrictTypes);
            $node->returnType = new PhpParserUnionType($unwrappedTypes);
            return $node;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function isUnionPossibleReturnsVoid($node) : bool
    {
        $inferReturnType = $this->returnTypeInferer->inferFunctionLike($node);
        if ($inferReturnType instanceof UnionType) {
            foreach ($inferReturnType->getTypes() as $type) {
                if ($type->isVoid()->yes()) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     * @return \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_
     */
    private function processSingleUnionType($node, UnionType $unionType, NullableType $nullableType)
    {
        $types = $unionType->getTypes();
        $returnType = $types[0] instanceof ObjectType && $types[1] instanceof NullType ? new NullableType(new FullyQualified($types[0]->getClassName())) : $nullableType;
        $node->returnType = $returnType;
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function shouldSkip($node, Scope $scope) : bool
    {
        if ($node->returnType !== null) {
            return \true;
        }
        if ($node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return \true;
        }
        return $this->isUnionPossibleReturnsVoid($node);
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\ComplexType $returnedStrictTypeNode
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     * @return \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_
     */
    private function refactorSingleReturnType(Return_ $return, $returnedStrictTypeNode, $functionLike)
    {
        $resolvedType = $this->nodeTypeResolver->getType($return);
        if ($resolvedType instanceof UnionType) {
            if (!$returnedStrictTypeNode instanceof NullableType) {
                return $functionLike;
            }
            return $this->processSingleUnionType($functionLike, $resolvedType, $returnedStrictTypeNode);
        }
        /** @var Name $returnType */
        $returnType = $resolvedType instanceof ObjectType ? new FullyQualified($resolvedType->getClassName()) : $returnedStrictTypeNode;
        $functionLike->returnType = $returnType;
        return $functionLike;
    }
}
