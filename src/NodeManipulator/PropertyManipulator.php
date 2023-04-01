<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use RectorPrefix202304\Doctrine\ORM\Mapping\ManyToMany;
use RectorPrefix202304\Doctrine\ORM\Mapping\Table;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Unset_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\Util\MultiInstanceofChecker;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Php80\NodeAnalyzer\PromotedPropertyResolver;
use Rector\ReadWrite\Guard\VariableToConstantGuard;
use Rector\ReadWrite\NodeAnalyzer\ReadWritePropertyAnalyzer;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
/**
 * For inspiration to improve this service,
 * @see examples of variable modifications in https://wiki.php.net/rfc/readonly_properties_v2#proposal
 */
final class PropertyManipulator
{
    /**
     * @var string[]|class-string<Table>[]
     */
    private const ALLOWED_NOT_READONLY_ANNOTATION_CLASS_OR_ATTRIBUTES = ['Doctrine\\ORM\\Mapping\\Entity', 'Doctrine\\ORM\\Mapping\\Table', 'Doctrine\\ORM\\Mapping\\MappedSuperclass'];
    /**
     * @var string[]|class-string<ManyToMany>[]
     */
    private const ALLOWED_READONLY_ANNOTATION_CLASS_OR_ATTRIBUTES = ['Doctrine\\ORM\\Mapping\\Id', 'Doctrine\\ORM\\Mapping\\Column', 'Doctrine\\ORM\\Mapping\\OneToMany', 'Doctrine\\ORM\\Mapping\\ManyToMany', 'Doctrine\\ORM\\Mapping\\ManyToOne', 'Doctrine\\ORM\\Mapping\\OneToOne', 'JMS\\Serializer\\Annotation\\Type'];
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\AssignManipulator
     */
    private $assignManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\ReadWrite\Guard\VariableToConstantGuard
     */
    private $variableToConstantGuard;
    /**
     * @readonly
     * @var \Rector\ReadWrite\NodeAnalyzer\ReadWritePropertyAnalyzer
     */
    private $readWritePropertyAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder
     */
    private $propertyFetchFinder;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PromotedPropertyResolver
     */
    private $promotedPropertyResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector
     */
    private $constructorAssignDetector;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Util\MultiInstanceofChecker
     */
    private $multiInstanceofChecker;
    public function __construct(\Rector\Core\NodeManipulator\AssignManipulator $assignManipulator, BetterNodeFinder $betterNodeFinder, VariableToConstantGuard $variableToConstantGuard, ReadWritePropertyAnalyzer $readWritePropertyAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, PropertyFetchFinder $propertyFetchFinder, ReflectionResolver $reflectionResolver, NodeNameResolver $nodeNameResolver, PhpAttributeAnalyzer $phpAttributeAnalyzer, NodeTypeResolver $nodeTypeResolver, PromotedPropertyResolver $promotedPropertyResolver, ConstructorAssignDetector $constructorAssignDetector, AstResolver $astResolver, PropertyFetchAnalyzer $propertyFetchAnalyzer, MultiInstanceofChecker $multiInstanceofChecker)
    {
        $this->assignManipulator = $assignManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->variableToConstantGuard = $variableToConstantGuard;
        $this->readWritePropertyAnalyzer = $readWritePropertyAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->reflectionResolver = $reflectionResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->promotedPropertyResolver = $promotedPropertyResolver;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->astResolver = $astResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->multiInstanceofChecker = $multiInstanceofChecker;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $propertyOrPromotedParam
     */
    public function isPropertyUsedInReadContext(Class_ $class, $propertyOrPromotedParam) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($propertyOrPromotedParam);
        if ($this->isAllowedReadOnly($propertyOrPromotedParam, $phpDocInfo)) {
            return \true;
        }
        $privatePropertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($class, $propertyOrPromotedParam);
        foreach ($privatePropertyFetches as $privatePropertyFetch) {
            if ($this->readWritePropertyAnalyzer->isRead($privatePropertyFetch)) {
                return \true;
            }
        }
        // has classLike $this->$variable call?
        $classLike = $this->betterNodeFinder->findParentType($propertyOrPromotedParam, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirst($classLike->stmts, function (Node $node) : bool {
            if (!$node instanceof PropertyFetch) {
                return \false;
            }
            if (!$this->readWritePropertyAnalyzer->isRead($node)) {
                return \false;
            }
            return $node->name instanceof Expr;
        });
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $propertyOrParam
     */
    public function isPropertyChangeableExceptConstructor($propertyOrParam) : bool
    {
        $class = $this->betterNodeFinder->findParentType($propertyOrParam, Class_::class);
        // does not have parent type ClassLike? Possibly parent is changed by other rule
        if (!$class instanceof Class_) {
            return \true;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        if ($phpDocInfo->hasByAnnotationClasses(self::ALLOWED_NOT_READONLY_ANNOTATION_CLASS_OR_ATTRIBUTES)) {
            return \true;
        }
        if ($this->phpAttributeAnalyzer->hasPhpAttributes($class, self::ALLOWED_NOT_READONLY_ANNOTATION_CLASS_OR_ATTRIBUTES)) {
            return \true;
        }
        $propertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($class, $propertyOrParam);
        foreach ($propertyFetches as $propertyFetch) {
            if ($this->isChangeableContext($propertyFetch)) {
                return \true;
            }
            // skip for constructor? it is allowed to set value in constructor method
            $propertyName = (string) $this->nodeNameResolver->getName($propertyFetch);
            $classMethod = $this->betterNodeFinder->findParentType($propertyFetch, ClassMethod::class);
            if ($this->isPropertyAssignedOnlyInConstructor($class, $propertyName, $classMethod)) {
                continue;
            }
            if ($this->assignManipulator->isLeftPartOfAssign($propertyFetch)) {
                return \true;
            }
            $isInUnset = (bool) $this->betterNodeFinder->findParentType($propertyFetch, Unset_::class);
            if ($isInUnset) {
                return \true;
            }
        }
        return \false;
    }
    public function isPropertyChangeable(Class_ $class, Property $property) : bool
    {
        $propertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($class, $property);
        foreach ($propertyFetches as $propertyFetch) {
            if ($this->isChangeableContext($propertyFetch)) {
                return \true;
            }
            if ($this->assignManipulator->isLeftPartOfAssign($propertyFetch)) {
                return \true;
            }
        }
        return \false;
    }
    public function resolveExistingClassPropertyNameByType(Class_ $class, ObjectType $objectType) : ?string
    {
        foreach ($class->getProperties() as $property) {
            $propertyType = $this->nodeTypeResolver->getType($property);
            if (!$propertyType->equals($objectType)) {
                continue;
            }
            return $this->nodeNameResolver->getName($property);
        }
        $promotedPropertyParams = $this->promotedPropertyResolver->resolveFromClass($class);
        foreach ($promotedPropertyParams as $promotedPropertyParam) {
            $paramType = $this->nodeTypeResolver->getType($promotedPropertyParam);
            if (!$paramType->equals($objectType)) {
                continue;
            }
            return $this->nodeNameResolver->getName($promotedPropertyParam);
        }
        return null;
    }
    public function isUsedByTrait(ClassReflection $classReflection, string $propertyName) : bool
    {
        foreach ($classReflection->getTraits() as $traitUse) {
            $trait = $this->astResolver->resolveClassFromName($traitUse->getName());
            if (!$trait instanceof Trait_) {
                continue;
            }
            if ($this->propertyFetchAnalyzer->containsLocalPropertyFetchName($trait, $propertyName)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $propertyOrPromotedParam
     */
    private function isAllowedReadOnly($propertyOrPromotedParam, PhpDocInfo $phpDocInfo) : bool
    {
        if ($phpDocInfo->hasByAnnotationClasses(self::ALLOWED_READONLY_ANNOTATION_CLASS_OR_ATTRIBUTES)) {
            return \true;
        }
        return $this->phpAttributeAnalyzer->hasPhpAttributes($propertyOrPromotedParam, self::ALLOWED_READONLY_ANNOTATION_CLASS_OR_ATTRIBUTES);
    }
    private function isPropertyAssignedOnlyInConstructor(Class_ $class, string $propertyName, ?ClassMethod $classMethod) : bool
    {
        if (!$classMethod instanceof ClassMethod) {
            return \false;
        }
        // there is property unset in Test class, so only check on __construct
        if (!$this->nodeNameResolver->isName($classMethod->name, MethodName::CONSTRUCT)) {
            return \false;
        }
        return $this->constructorAssignDetector->isPropertyAssigned($class, $propertyName);
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
     */
    private function isChangeableContext($propertyFetch) : bool
    {
        $parentNode = $propertyFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Node) {
            return \false;
        }
        if ($this->multiInstanceofChecker->isInstanceOf($parentNode, [PreInc::class, PreDec::class, PostInc::class, PostDec::class])) {
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }
        if (!$parentNode instanceof Node) {
            return \false;
        }
        if ($parentNode instanceof Arg) {
            $readArg = $this->variableToConstantGuard->isReadArg($parentNode);
            if (!$readArg) {
                return \true;
            }
            $caller = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($caller instanceof MethodCall || $caller instanceof StaticCall) {
                return $this->isFoundByRefParam($caller);
            }
        }
        if ($parentNode instanceof ArrayDimFetch) {
            return !$this->readWritePropertyAnalyzer->isRead($propertyFetch);
        }
        return $parentNode instanceof Unset_;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function isFoundByRefParam($node) : bool
    {
        $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if ($functionLikeReflection === null) {
            return \false;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($functionLikeReflection, $node, $scope);
        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            if ($parameterReflection->passedByReference()->yes()) {
                return \true;
            }
        }
        return \false;
    }
}
