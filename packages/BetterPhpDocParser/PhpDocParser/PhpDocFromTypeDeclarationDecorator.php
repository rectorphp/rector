<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use Rector\StaticTypeMapper\StaticTypeMapper;
use ReturnTypeWillChange;

final class PhpDocFromTypeDeclarationDecorator
{
    /**
     * @var class-string<ReturnTypeWillChange>
     */
    private const RETURN_TYPE_WILL_CHANGE_ATTRIBUTE = 'ReturnTypeWillChange';

    /**
     * @var array<string, array<string, string[]>>
     */
    private const ADD_RETURN_TYPE_WILL_CHANGE = [
        'PHPStan\Type\MixedType' => [
            'ArrayAccess' => ['offsetGet'],
        ],
    ];

    public function __construct(
        private readonly StaticTypeMapper $staticTypeMapper,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly PhpDocTypeChanger $phpDocTypeChanger,
        private readonly TypeUnwrapper $typeUnwrapper,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly PhpAttributeGroupFactory $phpAttributeGroupFactory,
        private readonly PhpAttributeAnalyzer $phpAttributeAnalyzer
    ) {
    }

    public function decorate(ClassMethod | Function_ | Closure | ArrowFunction $functionLike): void
    {
        if ($functionLike->returnType === null) {
            return;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $type);

        $functionLike->returnType = null;

        if (! $functionLike instanceof ClassMethod) {
            return;
        }

        $classLike = $this->betterNodeFinder->findParentByTypes($functionLike, [Class_::class, Interface_::class]);
        if (! $classLike instanceof ClassLike) {
            return;
        }

        if (! $this->isRequireReturnTypeWillChange($type::class, $classLike, $functionLike)) {
            return;
        }

        $attributeGroup = $this->phpAttributeGroupFactory->createFromClass(
            self::RETURN_TYPE_WILL_CHANGE_ATTRIBUTE
        );
        $functionLike->attrGroups[] = $attributeGroup;
    }

    /**
     * @param array<class-string<Type>> $requiredTypes
     */
    public function decorateParam(
        Param $param,
        ClassMethod | Function_ | Closure | ArrowFunction $functionLike,
        array $requiredTypes
    ): void {
        if ($param->type === null) {
            return;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);

        if (! $this->isMatchingType($type, $requiredTypes)) {
            return;
        }

        $this->moveParamTypeToParamDoc($functionLike, $param, $type);
    }

    public function decorateParamWithSpecificType(
        Param $param,
        ClassMethod | Function_ | Closure | ArrowFunction $functionLike,
        Type $requireType
    ): void {
        if ($param->type === null) {
            return;
        }

        if (! $this->isTypeMatch($param->type, $requireType)) {
            return;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $this->moveParamTypeToParamDoc($functionLike, $param, $type);
    }

    /**
     * @return bool True if node was changed
     */
    public function decorateReturnWithSpecificType(
        ClassMethod | Function_ | Closure | ArrowFunction $functionLike,
        Type $requireType
    ): bool {
        if ($functionLike->returnType === null) {
            return false;
        }

        if (! $this->isTypeMatch($functionLike->returnType, $requireType)) {
            return false;
        }

        $this->decorate($functionLike);
        return true;
    }

    private function isRequireReturnTypeWillChange(string $type, ClassLike $classLike, ClassMethod $classMethod): bool
    {
        if (! array_key_exists($type, self::ADD_RETURN_TYPE_WILL_CHANGE)) {
            return false;
        }

        $className = (string) $this->nodeNameResolver->getName($classLike);
        $objectClass = new ObjectType($className);
        $methodName = $this->nodeNameResolver->getName($classMethod);

        foreach (self::ADD_RETURN_TYPE_WILL_CHANGE[$type] as $class => $methods) {
            $objectClassConfig = new ObjectType($class);
            if (! $objectClassConfig->isSuperTypeOf($objectClass)->yes()) {
                continue;
            }

            if (! in_array($methodName, $methods, true)) {
                continue;
            }

            if ($this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, self::RETURN_TYPE_WILL_CHANGE_ATTRIBUTE)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function isTypeMatch(ComplexType|Identifier|Name $typeNode, Type $requireType): bool
    {
        $returnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($typeNode);

        // cover nullable union types
        if ($returnType instanceof UnionType) {
            $returnType = $this->typeUnwrapper->unwrapNullableType($returnType);
        }

        return $returnType::class === $requireType::class;
    }

    private function moveParamTypeToParamDoc(
        ClassMethod | Function_ | Closure | ArrowFunction $functionLike,
        Param $param,
        Type $type
    ): void {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $paramName = $this->nodeNameResolver->getName($param);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $type, $param, $paramName);

        $param->type = null;
    }

    /**
     * @param array<class-string<Type>> $requiredTypes
     */
    private function isMatchingType(Type $type, array $requiredTypes): bool
    {
        return in_array($type::class, $requiredTypes, true);
    }
}
