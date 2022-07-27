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
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\UnionTypesRectorTest
 */
final class UnionTypesRector extends AbstractRector implements MinPhpVersionInterface
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
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
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
    public function __construct(ReturnTagRemover $returnTagRemover, ParamTagRemover $paramTagRemover, ClassMethodParamVendorLockResolver $classMethodParamVendorLockResolver, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, UnionTypeAnalyzer $unionTypeAnalyzer, TypeFactory $typeFactory)
    {
        $this->returnTagRemover = $returnTagRemover;
        $this->paramTagRemover = $paramTagRemover;
        $this->classMethodParamVendorLockResolver = $classMethodParamVendorLockResolver;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
        $this->typeFactory = $typeFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change docs types to union types, where possible (properties are covered by TypedPropertiesRector)', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class];
    }
    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->hasChanged = \false;
        if ($node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->refactorParamTypes($node, $phpDocInfo);
        $this->refactorReturnType($node, $phpDocInfo);
        $this->paramTagRemover->removeParamTagsIfUseless($phpDocInfo, $node);
        if ($phpDocInfo->hasChanged()) {
            $this->hasChanged = \true;
        }
        $hasReturnChanged = $this->returnTagRemover->removeReturnTagIfUseless($phpDocInfo, $node);
        if ($hasReturnChanged) {
            $this->hasChanged = \true;
        }
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::UNION_TYPES;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function refactorParamTypes($functionLike, PhpDocInfo $phpDocInfo) : void
    {
        // skip parent class lock too, just to be safe in case of different parent docs
        if ($functionLike instanceof ClassMethod && $this->classMethodParamVendorLockResolver->isSoftLocked($functionLike)) {
            return;
        }
        foreach ($functionLike->getParams() as $param) {
            /** @var string $paramName */
            $paramName = $this->getName($param->var);
            $paramType = $phpDocInfo->getParamType($paramName);
            if (!$paramType instanceof UnionType) {
                continue;
            }
            if ($this->unionTypeAnalyzer->hasObjectWithoutClassType($paramType)) {
                $this->changeObjectWithoutClassType($param, $paramType);
                continue;
            }
            $uniqueatedParamType = $this->filterOutDuplicatedArrayTypes($paramType);
            if (!$uniqueatedParamType instanceof UnionType) {
                continue;
            }
            // mixed has to be standalone type, cannot be part of union type declaration
            if ($paramType->isSuperTypeOf(new MixedType())->yes()) {
                continue;
            }
            $phpParserUnionType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($uniqueatedParamType, TypeKind::PARAM);
            if ($this->shouldSkipParamTypeRefactor($param->type, $phpParserUnionType)) {
                continue;
            }
            $param->type = $phpParserUnionType;
            $this->hasChanged = \true;
        }
    }
    private function changeObjectWithoutClassType(Param $param, UnionType $unionType) : void
    {
        if (!$this->unionTypeAnalyzer->hasObjectWithoutClassTypeWithOnlyFullyQualifiedObjectType($unionType)) {
            return;
        }
        $param->type = new Name('object');
        $this->hasChanged = \true;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function refactorReturnType($functionLike, PhpDocInfo $phpDocInfo) : void
    {
        // do not override existing return type
        if ($functionLike->getReturnType() !== null) {
            return;
        }
        $returnType = $phpDocInfo->getReturnType();
        if (!$returnType instanceof UnionType) {
            return;
        }
        $uniqueatedReturnType = $this->filterOutDuplicatedArrayTypes($returnType);
        if (!$uniqueatedReturnType instanceof UnionType) {
            return;
        }
        // mixed has to be standalone type, cannot be part of union type declaration
        if ($uniqueatedReturnType->isSuperTypeOf(new MixedType())->yes()) {
            return;
        }
        $phpParserUnionType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($uniqueatedReturnType, TypeKind::RETURN);
        if (!$phpParserUnionType instanceof PhpParserUnionType) {
            return;
        }
        $functionLike->returnType = $phpParserUnionType;
        $this->hasChanged = \true;
    }
    /**
     * @return \PHPStan\Type\UnionType|\PHPStan\Type\Type
     */
    private function filterOutDuplicatedArrayTypes(UnionType $unionType)
    {
        $hasArrayType = \false;
        $singleArrayTypes = [];
        $originalTypeCount = \count($unionType->getTypes());
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof ArrayType) {
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
     * @param \PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\ComplexType|null $type
     * @param \PhpParser\Node\Name|\PhpParser\Node\ComplexType|\PhpParser\Node|null $phpParserUnionType
     */
    private function shouldSkipParamTypeRefactor($type, $phpParserUnionType) : bool
    {
        if (!$phpParserUnionType instanceof PhpParserUnionType) {
            return \true;
        }
        if ($type instanceof PhpParserUnionType) {
            return \true;
        }
        if (\count($phpParserUnionType->types) > 1) {
            return \false;
        }
        $firstType = $phpParserUnionType->types[0];
        if (!$firstType instanceof FullyQualified) {
            return \false;
        }
        if (!$type instanceof FullyQualified) {
            return \false;
        }
        return $type->toString() === $firstType->toString();
    }
}
