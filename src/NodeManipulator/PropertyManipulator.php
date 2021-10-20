<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\Guard\VariableToConstantGuard;
use Rector\ReadWrite\NodeAnalyzer\ReadWritePropertyAnalyzer;
use RectorPrefix20211020\Symplify\PackageBuilder\Php\TypeChecker;
/**
 * For inspiration to improve this service,
 * @see examples of variable modifications in https://wiki.php.net/rfc/readonly_properties_v2#proposal
 */
final class PropertyManipulator
{
    /**
     * @var \Rector\Core\NodeManipulator\AssignManipulator
     */
    private $assignManipulator;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\ReadWrite\Guard\VariableToConstantGuard
     */
    private $variableToConstantGuard;
    /**
     * @var \Rector\ReadWrite\NodeAnalyzer\ReadWritePropertyAnalyzer
     */
    private $readWritePropertyAnalyzer;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \Symplify\PackageBuilder\Php\TypeChecker
     */
    private $typeChecker;
    /**
     * @var \Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder
     */
    private $propertyFetchFinder;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Core\NodeManipulator\AssignManipulator $assignManipulator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\ReadWrite\Guard\VariableToConstantGuard $variableToConstantGuard, \Rector\ReadWrite\NodeAnalyzer\ReadWritePropertyAnalyzer $readWritePropertyAnalyzer, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \RectorPrefix20211020\Symplify\PackageBuilder\Php\TypeChecker $typeChecker, \Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder $propertyFetchFinder, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
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
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $propertyOrPromotedParam
     */
    public function isPropertyUsedInReadContext($propertyOrPromotedParam) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($propertyOrPromotedParam);
        // @todo attributes too
        if ($phpDocInfo->hasByAnnotationClasses(['Doctrine\\ORM\\Mapping\\Id', 'Doctrine\\ORM\\Mapping\\Column', 'Doctrine\\ORM\\Mapping\\OneToMany', 'Doctrine\\ORM\\Mapping\\ManyToMany', 'Doctrine\\ORM\\Mapping\\ManyToOne', 'Doctrine\\ORM\\Mapping\\OneToOne', 'JMS\\Serializer\\Annotation\\Type'])) {
            return \true;
        }
        // $propertyOrPromotedParam->attrGroups
        $privatePropertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($propertyOrPromotedParam);
        foreach ($privatePropertyFetches as $privatePropertyFetch) {
            if ($this->readWritePropertyAnalyzer->isRead($privatePropertyFetch)) {
                return \true;
            }
        }
        // has classLike $this->$variable call?
        /** @var ClassLike $classLike */
        $classLike = $propertyOrPromotedParam->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
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
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $propertyOrParam
     */
    public function isPropertyChangeableExceptConstructor($propertyOrParam) : bool
    {
        $propertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($propertyOrParam);
        foreach ($propertyFetches as $propertyFetch) {
            if ($this->isChangeableContext($propertyFetch)) {
                return \true;
            }
            // skip for constructor? it is allowed to set value in constructor method
            $classMethod = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
            if ($classMethod instanceof \PhpParser\Node\Stmt\ClassMethod && $this->nodeNameResolver->isName($classMethod->name, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
                continue;
            }
            if ($this->assignManipulator->isLeftPartOfAssign($propertyFetch)) {
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
