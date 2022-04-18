<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use RectorPrefix20220418\Doctrine\ORM\Mapping\ManyToMany;
use RectorPrefix20220418\Doctrine\ORM\Mapping\Table;
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
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Unset_;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Php80\NodeAnalyzer\PromotedPropertyResolver;
use Rector\ReadWrite\Guard\VariableToConstantGuard;
use Rector\ReadWrite\NodeAnalyzer\ReadWritePropertyAnalyzer;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use RectorPrefix20220418\Symplify\PackageBuilder\Php\TypeChecker;
/**
 * For inspiration to improve this service,
 * @see examples of variable modifications in https://wiki.php.net/rfc/readonly_properties_v2#proposal
 */
final class PropertyManipulator
{
    /**
     * @var string[]|class-string<Table>[]
     */
    private const ALLOWED_NOT_READONLY_ANNOTATION_CLASS_OR_ATTRIBUTES = ['Doctrine\\ORM\\Mapping\\Entity', 'Doctrine\\ORM\\Mapping\\Table'];
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
     * @var \Symplify\PackageBuilder\Php\TypeChecker
     */
    private $typeChecker;
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
    public function __construct(\Rector\Core\NodeManipulator\AssignManipulator $assignManipulator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\ReadWrite\Guard\VariableToConstantGuard $variableToConstantGuard, \Rector\ReadWrite\NodeAnalyzer\ReadWritePropertyAnalyzer $readWritePropertyAnalyzer, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \RectorPrefix20220418\Symplify\PackageBuilder\Php\TypeChecker $typeChecker, \Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder $propertyFetchFinder, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer $phpAttributeAnalyzer, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Php80\NodeAnalyzer\PromotedPropertyResolver $promotedPropertyResolver, \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector $constructorAssignDetector, \Rector\Core\PhpParser\AstResolver $astResolver, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->assignManipulator = $assignManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->variableToConstantGuard = $variableToConstantGuard;
        $this->readWritePropertyAnalyzer = $readWritePropertyAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeChecker = $typeChecker;
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->reflectionResolver = $reflectionResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->promotedPropertyResolver = $promotedPropertyResolver;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->astResolver = $astResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\Property $propertyOrPromotedParam
     */
    public function isAllowedReadOnly($propertyOrPromotedParam, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : bool
    {
        if ($phpDocInfo->hasByAnnotationClasses(self::ALLOWED_READONLY_ANNOTATION_CLASS_OR_ATTRIBUTES)) {
            return \true;
        }
        return $this->phpAttributeAnalyzer->hasPhpAttributes($propertyOrPromotedParam, self::ALLOWED_READONLY_ANNOTATION_CLASS_OR_ATTRIBUTES);
    }
    /**
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\Property $propertyOrPromotedParam
     */
    public function isPropertyUsedInReadContext($propertyOrPromotedParam) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($propertyOrPromotedParam);
        if ($this->isAllowedReadOnly($propertyOrPromotedParam, $phpDocInfo)) {
            return \true;
        }
        $privatePropertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($propertyOrPromotedParam);
        foreach ($privatePropertyFetches as $privatePropertyFetch) {
            if ($this->readWritePropertyAnalyzer->isRead($privatePropertyFetch)) {
                return \true;
            }
        }
        // has classLike $this->$variable call?
        $classLike = $this->betterNodeFinder->findParentType($propertyOrPromotedParam, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirst($classLike->stmts, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return \false;
            }
            if (!$this->readWritePropertyAnalyzer->isRead($node)) {
                return \false;
            }
            return $node->name instanceof \PhpParser\Node\Expr;
        });
    }
    /**
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\Property $propertyOrParam
     */
    public function isPropertyChangeableExceptConstructor($propertyOrParam) : bool
    {
        $classLike = $this->betterNodeFinder->findParentType($propertyOrParam, \PhpParser\Node\Stmt\ClassLike::class);
        // does not has parent type ClassLike? Possibly parent is changed by other rule
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \true;
        }
        // Property or Param in interface? return true early as no property in interface
        if ($classLike instanceof \PhpParser\Node\Stmt\Interface_) {
            return \true;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classLike);
        if ($phpDocInfo->hasByAnnotationClasses(self::ALLOWED_NOT_READONLY_ANNOTATION_CLASS_OR_ATTRIBUTES)) {
            return \true;
        }
        if ($this->phpAttributeAnalyzer->hasPhpAttributes($classLike, self::ALLOWED_NOT_READONLY_ANNOTATION_CLASS_OR_ATTRIBUTES)) {
            return \true;
        }
        $propertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($propertyOrParam);
        foreach ($propertyFetches as $propertyFetch) {
            if ($this->isChangeableContext($propertyFetch)) {
                return \true;
            }
            // skip for constructor? it is allowed to set value in constructor method
            $propertyName = (string) $this->nodeNameResolver->getName($propertyFetch);
            $classMethod = $this->betterNodeFinder->findParentType($propertyFetch, \PhpParser\Node\Stmt\ClassMethod::class);
            if ($this->isPropertyAssignedOnlyInConstructor($classLike, $propertyName, $classMethod)) {
                continue;
            }
            if ($this->assignManipulator->isLeftPartOfAssign($propertyFetch)) {
                return \true;
            }
            $isInUnset = (bool) $this->betterNodeFinder->findParentType($propertyFetch, \PhpParser\Node\Stmt\Unset_::class);
            if ($isInUnset) {
                return \true;
            }
        }
        return \false;
    }
    public function isPropertyChangeable(\PhpParser\Node\Stmt\Property $property) : bool
    {
        $propertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($property);
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
    public function resolveExistingClassPropertyNameByType(\PhpParser\Node\Stmt\Class_ $class, \PHPStan\Type\Type $type) : ?string
    {
        foreach ($class->getProperties() as $property) {
            $propertyType = $this->nodeTypeResolver->getType($property);
            if (!$propertyType->equals($type)) {
                continue;
            }
            return $this->nodeNameResolver->getName($property);
        }
        $promotedPropertyParams = $this->promotedPropertyResolver->resolveFromClass($class);
        foreach ($promotedPropertyParams as $promotedPropertyParam) {
            $paramType = $this->nodeTypeResolver->getType($promotedPropertyParam);
            if (!$paramType->equals($type)) {
                continue;
            }
            return $this->nodeNameResolver->getName($promotedPropertyParam);
        }
        return null;
    }
    public function isUsedByTrait(\PhpParser\Node\Stmt\Class_ $class, string $propertyName) : bool
    {
        foreach ($class->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $traitName) {
                $trait = $this->astResolver->resolveClassFromName($traitName->toString());
                if (!$trait instanceof \PhpParser\Node\Stmt\Trait_) {
                    continue;
                }
                if ($this->propertyFetchAnalyzer->containsLocalPropertyFetchName($trait, $propertyName)) {
                    return \true;
                }
            }
        }
        return \false;
    }
    private function isPropertyAssignedOnlyInConstructor(\PhpParser\Node\Stmt\ClassLike $classLike, string $propertyName, ?\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        // there is property unset in Test class, so only check on __construct
        if (!$this->nodeNameResolver->isName($classMethod->name, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return \false;
        }
        return $this->constructorAssignDetector->isPropertyAssigned($classLike, $propertyName);
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
     */
    private function isChangeableContext($propertyFetch) : bool
    {
        $parent = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            return \false;
        }
        if ($this->typeChecker->isInstanceOf($parent, [\PhpParser\Node\Expr\PreInc::class, \PhpParser\Node\Expr\PreDec::class, \PhpParser\Node\Expr\PostInc::class, \PhpParser\Node\Expr\PostDec::class])) {
            $parent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        }
        if (!$parent instanceof \PhpParser\Node) {
            return \false;
        }
        if ($parent instanceof \PhpParser\Node\Arg) {
            $readArg = $this->variableToConstantGuard->isReadArg($parent);
            if (!$readArg) {
                return \true;
            }
            $caller = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($caller instanceof \PhpParser\Node\Expr\MethodCall || $caller instanceof \PhpParser\Node\Expr\StaticCall) {
                return $this->isFoundByRefParam($caller);
            }
        }
        if ($parent instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return !$this->readWritePropertyAnalyzer->isRead($propertyFetch);
        }
        return \false;
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
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($functionLikeReflection->getVariants());
        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            if ($parameterReflection->passedByReference()->yes()) {
                return \true;
            }
        }
        return \false;
    }
}
