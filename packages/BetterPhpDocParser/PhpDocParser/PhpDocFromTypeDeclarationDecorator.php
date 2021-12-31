<?php

declare (strict_types=1);
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
use RectorPrefix20211231\ReturnTypeWillChange;
final class PhpDocFromTypeDeclarationDecorator
{
    /**
     * @var class-string<ReturnTypeWillChange>
     */
    public const RETURN_TYPE_WILL_CHANGE_ATTRIBUTE = 'ReturnTypeWillChange';
    /**
     * @var array<string, array<string, string[]>>
     */
    public const ADD_RETURN_TYPE_WILL_CHANGE = ['PHPStan\\Type\\MixedType' => ['ArrayAccess' => ['offsetGet']]];
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper
     */
    private $typeUnwrapper;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\Printer\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper $typeUnwrapper, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\PhpAttribute\Printer\PhpAttributeGroupFactory $phpAttributeGroupFactory, \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->typeUnwrapper = $typeUnwrapper;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function decorate($functionLike) : void
    {
        if ($functionLike->returnType === null) {
            return;
        }
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $type);
        $functionLike->returnType = null;
        if (!$functionLike instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        $classLike = $this->betterNodeFinder->findParentByTypes($functionLike, [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\Interface_::class]);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return;
        }
        if (!$this->isRequireReturnTypeWillChange(\get_class($type), $classLike, $functionLike)) {
            return;
        }
        $attributeGroup = $this->phpAttributeGroupFactory->createFromClass(self::RETURN_TYPE_WILL_CHANGE_ATTRIBUTE);
        $functionLike->attrGroups[] = $attributeGroup;
    }
    /**
     * @param array<class-string<Type>> $requiredTypes
     * @param \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function decorateParam(\PhpParser\Node\Param $param, $functionLike, array $requiredTypes) : void
    {
        if ($param->type === null) {
            return;
        }
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        if (!$this->isMatchingType($type, $requiredTypes)) {
            return;
        }
        $this->moveParamTypeToParamDoc($functionLike, $param, $type);
    }
    /**
     * @param \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function decorateParamWithSpecificType(\PhpParser\Node\Param $param, $functionLike, \PHPStan\Type\Type $requireType) : void
    {
        if ($param->type === null) {
            return;
        }
        if (!$this->isTypeMatch($param->type, $requireType)) {
            return;
        }
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $this->moveParamTypeToParamDoc($functionLike, $param, $type);
    }
    /**
     * @return bool True if node was changed
     * @param \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function decorateReturnWithSpecificType($functionLike, \PHPStan\Type\Type $requireType) : bool
    {
        if ($functionLike->returnType === null) {
            return \false;
        }
        if (!$this->isTypeMatch($functionLike->returnType, $requireType)) {
            return \false;
        }
        $this->decorate($functionLike);
        return \true;
    }
    private function isRequireReturnTypeWillChange(string $type, \PhpParser\Node\Stmt\ClassLike $classLike, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!\array_key_exists($type, self::ADD_RETURN_TYPE_WILL_CHANGE)) {
            return \false;
        }
        $className = (string) $this->nodeNameResolver->getName($classLike);
        $objectClass = new \PHPStan\Type\ObjectType($className);
        $methodName = $this->nodeNameResolver->getName($classMethod);
        foreach (self::ADD_RETURN_TYPE_WILL_CHANGE[$type] as $class => $methods) {
            $objectClassConfig = new \PHPStan\Type\ObjectType($class);
            if (!$objectClassConfig->isSuperTypeOf($objectClass)->yes()) {
                continue;
            }
            if (!\in_array($methodName, $methods, \true)) {
                continue;
            }
            if ($this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, self::RETURN_TYPE_WILL_CHANGE_ATTRIBUTE)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|\PhpParser\Node\Name $typeNode
     */
    private function isTypeMatch($typeNode, \PHPStan\Type\Type $requireType) : bool
    {
        $returnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($typeNode);
        // cover nullable union types
        if ($returnType instanceof \PHPStan\Type\UnionType) {
            $returnType = $this->typeUnwrapper->unwrapNullableType($returnType);
        }
        return \get_class($returnType) === \get_class($requireType);
    }
    /**
     * @param \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function moveParamTypeToParamDoc($functionLike, \PhpParser\Node\Param $param, \PHPStan\Type\Type $type) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $paramName = $this->nodeNameResolver->getName($param);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $type, $param, $paramName);
        $param->type = null;
    }
    /**
     * @param array<class-string<Type>> $requiredTypes
     */
    private function isMatchingType(\PHPStan\Type\Type $type, array $requiredTypes) : bool
    {
        return \in_array(\get_class($type), $requiredTypes, \true);
    }
}
