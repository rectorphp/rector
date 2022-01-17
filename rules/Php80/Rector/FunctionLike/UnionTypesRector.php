<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
use Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\UnionTypesRectorTest
 */
final class UnionTypesRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover
     */
    private $returnTagRemover;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover
     */
    private $paramTagRemover;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver
     */
    private $classMethodParamVendorLockResolver;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer
     */
    private $unionTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(\Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover $returnTagRemover, \Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover $paramTagRemover, \Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver $classMethodParamVendorLockResolver, \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer $unionTypeAnalyzer, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory)
    {
        $this->returnTagRemover = $returnTagRemover;
        $this->paramTagRemover = $paramTagRemover;
        $this->classMethodParamVendorLockResolver = $classMethodParamVendorLockResolver;
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
        $this->typeFactory = $typeFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change docs types to union types, where possible (properties are covered by TypedPropertiesRector)', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param array|int $number
     * @return bool|float
     */
    public function go($number)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function go(array|int $number): bool|float
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Expr\ArrowFunction::class];
    }
    /**
     * @param ClassMethod | Function_ | Closure | ArrowFunction $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $this->hasChanged = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->refactorParamTypes($node, $phpDocInfo);
        $this->refactorReturnType($node, $phpDocInfo);
        $this->paramTagRemover->removeParamTagsIfUseless($phpDocInfo, $node);
        $this->returnTagRemover->removeReturnTagIfUseless($phpDocInfo, $node);
        if ($phpDocInfo->hasChanged()) {
            $this->hasChanged = \true;
        }
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::UNION_TYPES;
    }
    /**
     * @param \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function refactorParamTypes($functionLike, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : void
    {
        // skip parent class lock too, just to be safe in case of different parent docs
        if ($functionLike instanceof \PhpParser\Node\Stmt\ClassMethod && $this->classMethodParamVendorLockResolver->isSoftLocked($functionLike)) {
            return;
        }
        foreach ($functionLike->getParams() as $param) {
            /** @var string $paramName */
            $paramName = $this->getName($param->var);
            $paramType = $phpDocInfo->getParamType($paramName);
            if (!$paramType instanceof \PHPStan\Type\UnionType) {
                continue;
            }
            if ($this->unionTypeAnalyzer->hasObjectWithoutClassType($paramType)) {
                $this->changeObjectWithoutClassType($param, $paramType);
                continue;
            }
            $uniqueatedParamType = $this->filterOutDuplicatedArrayTypes($paramType);
            if (!$uniqueatedParamType instanceof \PHPStan\Type\UnionType) {
                continue;
            }
            // mixed has to be standalone type, cannot be part of union type declaration
            if ($paramType->isSuperTypeOf(new \PHPStan\Type\MixedType())->yes()) {
                continue;
            }
            $phpParserUnionType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($uniqueatedParamType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PARAM());
            if ($this->shouldSkipParamTypeRefactor($param->type, $phpParserUnionType)) {
                continue;
            }
            $param->type = $phpParserUnionType;
            $this->hasChanged = \true;
        }
    }
    private function changeObjectWithoutClassType(\PhpParser\Node\Param $param, \PHPStan\Type\UnionType $unionType) : void
    {
        if (!$this->unionTypeAnalyzer->hasObjectWithoutClassTypeWithOnlyFullyQualifiedObjectType($unionType)) {
            return;
        }
        $param->type = new \PhpParser\Node\Name('object');
        $this->hasChanged = \true;
    }
    /**
     * @param \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function refactorReturnType($functionLike, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : void
    {
        // do not override existing return type
        if ($functionLike->getReturnType() !== null) {
            return;
        }
        $returnType = $phpDocInfo->getReturnType();
        if (!$returnType instanceof \PHPStan\Type\UnionType) {
            return;
        }
        $uniqueatedReturnType = $this->filterOutDuplicatedArrayTypes($returnType);
        if (!$uniqueatedReturnType instanceof \PHPStan\Type\UnionType) {
            return;
        }
        // mixed has to be standalone type, cannot be part of union type declaration
        if ($uniqueatedReturnType->isSuperTypeOf(new \PHPStan\Type\MixedType())->yes()) {
            return;
        }
        $phpParserUnionType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($uniqueatedReturnType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::RETURN());
        if (!$phpParserUnionType instanceof \PhpParser\Node\UnionType) {
            return;
        }
        $functionLike->returnType = $phpParserUnionType;
        $this->hasChanged = \true;
    }
    /**
     * @return \PHPStan\Type\Type|\PHPStan\Type\UnionType
     */
    private function filterOutDuplicatedArrayTypes(\PHPStan\Type\UnionType $unionType)
    {
        $hasArrayType = \false;
        $singleArrayTypes = [];
        $originalTypeCount = \count($unionType->getTypes());
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof \PHPStan\Type\ArrayType) {
                if ($hasArrayType) {
                    continue;
                }
                $singleArrayTypes[] = $unionedType;
                $hasArrayType = \true;
                continue;
            }
            $singleArrayTypes[] = $unionedType;
        }
        if ($originalTypeCount === \count($singleArrayTypes)) {
            return $unionType;
        }
        return $this->typeFactory->createMixedPassedOrUnionType($singleArrayTypes);
    }
    /**
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|\PhpParser\Node\Name|null $type
     * @param \PhpParser\Node|\PhpParser\Node\ComplexType|\PhpParser\Node\Name|null $phpParserUnionType
     */
    private function shouldSkipParamTypeRefactor($type, $phpParserUnionType) : bool
    {
        if (!$phpParserUnionType instanceof \PhpParser\Node\UnionType) {
            return \true;
        }
        if ($type instanceof \PhpParser\Node\UnionType) {
            return \true;
        }
        if (\count($phpParserUnionType->types) > 1) {
            return \false;
        }
        $firstType = $phpParserUnionType->types[0];
        if (!$firstType instanceof \PhpParser\Node\Name\FullyQualified) {
            return \false;
        }
        if (!$type instanceof \PhpParser\Node\Name\FullyQualified) {
            return \false;
        }
        return $type->toString() === $firstType->toString();
    }
}
