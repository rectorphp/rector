<?php

declare (strict_types=1);
namespace Rector\PhpDocDecorator;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php\PhpVersionProvider;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\PhpParser\AstResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\SelfStaticType;
use Rector\ValueObject\ClassMethodWillChangeReturnType;
use Rector\ValueObject\PhpVersionFeature;
/**
 * @see https://wiki.php.net/rfc/internal_method_return_types#proposal
 */
final class PhpDocFromTypeDeclarationDecorator
{
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    /**
     * @readonly
     */
    private PhpAttributeGroupFactory $phpAttributeGroupFactory;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @var ClassMethodWillChangeReturnType[]
     */
    private array $classMethodWillChangeReturnTypes = [];
    public function __construct(StaticTypeMapper $staticTypeMapper, PhpDocInfoFactory $phpDocInfoFactory, NodeNameResolver $nodeNameResolver, PhpDocTypeChanger $phpDocTypeChanger, PhpAttributeGroupFactory $phpAttributeGroupFactory, ReflectionResolver $reflectionResolver, PhpAttributeAnalyzer $phpAttributeAnalyzer, PhpVersionProvider $phpVersionProvider, AstResolver $astResolver)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->reflectionResolver = $reflectionResolver;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->astResolver = $astResolver;
        $this->classMethodWillChangeReturnTypes = [
            // @todo how to make list complete? is the method list needed or can we use just class names?
            new ClassMethodWillChangeReturnType('ArrayAccess', 'offsetGet'),
            new ClassMethodWillChangeReturnType('ArrayAccess', 'getIterator'),
        ];
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    public function decorateReturn($functionLike) : void
    {
        if (!$functionLike->returnType instanceof Node) {
            return;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        $returnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        $returnDocType = $returnTagValueNode instanceof ReturnTagValueNode ? $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($returnTagValueNode, $functionLike->returnType) : $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        // if nullable is supported, downgrade to that one
        if ($this->isNullableSupportedAndPossible($returnType)) {
            $functionLike->returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);
            return;
        }
        $this->phpDocTypeChanger->changeReturnType($functionLike, $phpDocInfo, $returnDocType);
        $functionLike->returnType = null;
        if (!$functionLike instanceof ClassMethod) {
            return;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($functionLike);
        if (!$classReflection instanceof ClassReflection || !$classReflection->isInterface() && !$classReflection->isClass()) {
            return;
        }
        $ancestors = \array_filter($classReflection->getAncestors(), static fn(ClassReflection $ancestor): bool => $classReflection->getName() !== $ancestor->getName());
        foreach ($ancestors as $ancestor) {
            $classLike = $this->astResolver->resolveClassFromClassReflection($ancestor);
            if (!$classLike instanceof ClassLike) {
                continue;
            }
            $classMethod = $classLike->getMethod($functionLike->name->toString());
            if (!$classMethod instanceof ClassMethod) {
                continue;
            }
            $returnType = $classMethod->returnType;
            if ($returnType instanceof Node && $returnType instanceof FullyQualified) {
                $functionLike->returnType = new FullyQualified($returnType->toString());
                break;
            }
        }
        if (!$this->isRequireReturnTypeWillChange($classReflection, $functionLike)) {
            return;
        }
        $functionLike->attrGroups[] = $this->phpAttributeGroupFactory->createFromClass('ReturnTypeWillChange');
    }
    /**
     * @param array<class-string<Type>> $requiredTypes
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    public function decorateParam(Param $param, $functionLike, array $requiredTypes) : void
    {
        if (!$param->type instanceof Node) {
            return;
        }
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        if (!$this->isMatchingType($type, $requiredTypes)) {
            return;
        }
        if ($this->isNullableSupportedAndPossible($type)) {
            $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PARAM);
            return;
        }
        $this->moveParamTypeToParamDoc($functionLike, $param, $type);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    public function decorateParamWithSpecificType(Param $param, $functionLike, Type $requireType) : bool
    {
        if (!$param->type instanceof Node) {
            return \false;
        }
        if (!$this->isTypeMatch($param->type, $requireType)) {
            return \false;
        }
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        if ($this->isNullableSupportedAndPossible($type)) {
            $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PARAM);
            return \true;
        }
        $this->moveParamTypeToParamDoc($functionLike, $param, $type);
        return \true;
    }
    /**
     * @return bool True if node was changed
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    public function decorateReturnWithSpecificType($functionLike, Type $requireType) : bool
    {
        if (!$functionLike->returnType instanceof Node) {
            return \false;
        }
        if (!$this->isTypeMatch($functionLike->returnType, $requireType)) {
            return \false;
        }
        $this->decorateReturn($functionLike);
        return \true;
    }
    private function isRequireReturnTypeWillChange(ClassReflection $classReflection, ClassMethod $classMethod) : bool
    {
        if ($classReflection->isAnonymous()) {
            return \false;
        }
        $methodName = $classMethod->name->toString();
        // support for will return change type in case of removed return doc type
        // @see https://php.watch/versions/8.1/ReturnTypeWillChange
        foreach ($this->classMethodWillChangeReturnTypes as $classMethodWillChangeReturnType) {
            if ($classMethodWillChangeReturnType->getMethodName() !== $methodName) {
                continue;
            }
            if (!$classReflection->is($classMethodWillChangeReturnType->getClassName())) {
                continue;
            }
            if ($this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, 'ReturnTypeWillChange')) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|\PhpParser\Node\Name $typeNode
     */
    private function isTypeMatch($typeNode, Type $requireType) : bool
    {
        $returnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($typeNode);
        if ($returnType instanceof SelfStaticType) {
            $returnType = new ThisType($returnType->getClassReflection());
        }
        // cover nullable union types
        if ($returnType instanceof UnionType) {
            $returnType = TypeCombinator::removeNull($returnType);
        }
        if ($returnType instanceof ObjectType) {
            return $returnType->equals($requireType);
        }
        return \get_class($returnType) === \get_class($requireType);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function moveParamTypeToParamDoc($functionLike, Param $param, Type $type) : void
    {
        $param->type = null;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $paramName = $this->nodeNameResolver->getName($param);
        $phpDocParamType = $phpDocInfo->getParamType($paramName);
        if (!$type instanceof MixedType && \get_class($type) === \get_class($phpDocParamType)) {
            return;
        }
        $this->phpDocTypeChanger->changeParamType($functionLike, $phpDocInfo, $type, $param, $paramName);
    }
    /**
     * @param array<class-string<Type>> $requiredTypes
     */
    private function isMatchingType(Type $type, array $requiredTypes) : bool
    {
        return \in_array(\get_class($type), $requiredTypes, \true);
    }
    private function isNullableSupportedAndPossible(Type $type) : bool
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::NULLABLE_TYPE)) {
            return \false;
        }
        if (!$type instanceof UnionType) {
            return \false;
        }
        if (\count($type->getTypes()) !== 2) {
            return \false;
        }
        return TypeCombinator::containsNull($type);
    }
}
