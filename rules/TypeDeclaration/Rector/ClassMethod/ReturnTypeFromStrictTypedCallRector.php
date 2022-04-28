<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnStrictTypeAnalyzer;
use Rector\TypeDeclaration\NodeAnalyzer\TypeNodeUnwrapper;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\ReturnTypeFromStrictTypedCallRectorTest
 */
final class ReturnTypeFromStrictTypedCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\TypeNodeUnwrapper
     */
    private $typeNodeUnwrapper;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnStrictTypeAnalyzer
     */
    private $returnStrictTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\TypeDeclaration\NodeAnalyzer\TypeNodeUnwrapper $typeNodeUnwrapper, \Rector\TypeDeclaration\NodeAnalyzer\ReturnStrictTypeAnalyzer $returnStrictTypeAnalyzer, \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer $returnTypeInferer, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->typeNodeUnwrapper = $typeNodeUnwrapper;
        $this->returnStrictTypeAnalyzer = $returnStrictTypeAnalyzer;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add return type from strict return type of call', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Expr\ArrowFunction::class];
    }
    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->isSkipped($node)) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\ArrowFunction) {
            return $this->processArrowFunction($node);
        }
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->find((array) $node->stmts, function (\PhpParser\Node $n) use($node) : bool {
            $currentFunctionLike = $this->betterNodeFinder->findParentType($n, \PhpParser\Node\FunctionLike::class);
            if ($currentFunctionLike === $node) {
                return $n instanceof \PhpParser\Node\Stmt\Return_;
            }
            $currentReturn = $this->betterNodeFinder->findParentType($n, \PhpParser\Node\Stmt\Return_::class);
            if (!$currentReturn instanceof \PhpParser\Node\Stmt\Return_) {
                return \false;
            }
            $currentFunctionLike = $this->betterNodeFinder->findParentType($currentReturn, \PhpParser\Node\FunctionLike::class);
            if ($currentFunctionLike !== $node) {
                return \false;
            }
            return $n instanceof \PhpParser\Node\Stmt\Return_;
        });
        $returnedStrictTypes = $this->returnStrictTypeAnalyzer->collectStrictReturnTypes($returns);
        if ($returnedStrictTypes === []) {
            return null;
        }
        if (\count($returnedStrictTypes) === 1) {
            return $this->refactorSingleReturnType($returns[0], $returnedStrictTypes[0], $node);
        }
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::UNION_TYPES)) {
            /** @var PhpParserUnionType[] $returnedStrictTypes */
            $unwrappedTypes = $this->typeNodeUnwrapper->unwrapNullableUnionTypes($returnedStrictTypes);
            $returnType = new \PhpParser\Node\UnionType($unwrappedTypes);
            $node->returnType = $returnType;
            return $node;
        }
        return null;
    }
    private function processArrowFunction(\PhpParser\Node\Expr\ArrowFunction $arrowFunction) : ?\PhpParser\Node\Expr\ArrowFunction
    {
        $resolvedType = $this->nodeTypeResolver->getType($arrowFunction->expr);
        // void type is not accepted for arrow functions - https://www.php.net/manual/en/functions.arrow.php#125673
        if ($resolvedType instanceof \PHPStan\Type\VoidType) {
            return null;
        }
        $returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($resolvedType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::RETURN());
        if (!$returnType instanceof \PhpParser\Node) {
            return null;
        }
        $arrowFunction->returnType = $returnType;
        return $arrowFunction;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function isUnionPossibleReturnsVoid($node) : bool
    {
        $inferReturnType = $this->returnTypeInferer->inferFunctionLike($node);
        if ($inferReturnType instanceof \PHPStan\Type\UnionType) {
            foreach ($inferReturnType->getTypes() as $type) {
                if ($type instanceof \PHPStan\Type\VoidType) {
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
    private function processSingleUnionType($node, \PHPStan\Type\UnionType $unionType, \PhpParser\Node\NullableType $nullableType)
    {
        $types = $unionType->getTypes();
        $returnType = $types[0] instanceof \PHPStan\Type\ObjectType && $types[1] instanceof \PHPStan\Type\NullType ? new \PhpParser\Node\NullableType(new \PhpParser\Node\Name\FullyQualified($types[0]->getClassName())) : $nullableType;
        $node->returnType = $returnType;
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $node
     */
    private function isSkipped($node) : bool
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::SCALAR_TYPES)) {
            return \true;
        }
        if ($node instanceof \PhpParser\Node\Expr\ArrowFunction) {
            return $node->returnType !== null;
        }
        if ($node->returnType !== null) {
            return \true;
        }
        if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return $this->isUnionPossibleReturnsVoid($node);
        }
        if (!$node->isMagic()) {
            return $this->isUnionPossibleReturnsVoid($node);
        }
        return \true;
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType $returnedStrictTypeNode
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     * @return \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_
     */
    private function refactorSingleReturnType(\PhpParser\Node\Stmt\Return_ $return, $returnedStrictTypeNode, $functionLike)
    {
        $resolvedType = $this->nodeTypeResolver->getType($return);
        if ($resolvedType instanceof \PHPStan\Type\UnionType) {
            if (!$returnedStrictTypeNode instanceof \PhpParser\Node\NullableType) {
                return $functionLike;
            }
            return $this->processSingleUnionType($functionLike, $resolvedType, $returnedStrictTypeNode);
        }
        /** @var Name $returnType */
        $returnType = $resolvedType instanceof \PHPStan\Type\ObjectType ? new \PhpParser\Node\Name\FullyQualified($resolvedType->getClassName()) : $returnedStrictTypeNode;
        $functionLike->returnType = $returnType;
        return $functionLike;
    }
}
