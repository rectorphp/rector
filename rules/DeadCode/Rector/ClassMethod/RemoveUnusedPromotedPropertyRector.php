<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\TraitUse;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\DeadCode\NodeAnalyzer\PropertyWriteonlyAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\ValueObject\Visibility;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector\RemoveUnusedPromotedPropertyRectorTest
 */
final class RemoveUnusedPromotedPropertyRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private PropertyFetchFinder $propertyFetchFinder;
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    /**
     * @readonly
     */
    private PropertyWriteonlyAnalyzer $propertyWriteonlyAnalyzer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PhpDocTagRemover $phpDocTagRemover;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(PropertyFetchFinder $propertyFetchFinder, VisibilityManipulator $visibilityManipulator, PropertyWriteonlyAnalyzer $propertyWriteonlyAnalyzer, BetterNodeFinder $betterNodeFinder, ReflectionResolver $reflectionResolver, PhpDocInfoFactory $phpDocInfoFactory, PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater)
    {
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->propertyWriteonlyAnalyzer = $propertyWriteonlyAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionResolver = $reflectionResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused promoted property', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private $someUnusedDependency,
        private $usedDependency
    ) {
    }

    public function getUsedDependency()
    {
        return $this->usedDependency;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private $usedDependency
    ) {
    }

    public function getUsedDependency()
    {
        return $this->usedDependency;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        if ($constructClassMethod->params === []) {
            return null;
        }
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $hasChanged = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($constructClassMethod);
        foreach ($constructClassMethod->params as $key => $param) {
            // only private local scope; removing public property might be dangerous
            if (!$this->visibilityManipulator->hasVisibility($param, Visibility::PRIVATE)) {
                continue;
            }
            $paramName = $this->getName($param);
            $propertyFetches = $this->propertyFetchFinder->findLocalPropertyFetchesByName($node, $paramName);
            if ($propertyFetches !== []) {
                continue;
            }
            if (!$this->propertyWriteonlyAnalyzer->arePropertyFetchesExclusivelyBeingAssignedTo($propertyFetches)) {
                continue;
            }
            // always changed on below code
            $hasChanged = \true;
            // is variable used? only remove property, keep param
            $variable = $this->betterNodeFinder->findVariableOfName((array) $constructClassMethod->stmts, $paramName);
            if ($variable instanceof Variable) {
                $param->flags = 0;
                continue;
            }
            if ($phpDocInfo instanceof PhpDocInfo) {
                $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
                if ($paramTagValueNode instanceof ParamTagValueNode) {
                    $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $paramTagValueNode);
                    $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($constructClassMethod);
                }
            }
            // remove param
            unset($constructClassMethod->params[$key]);
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::PROPERTY_PROMOTION;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        if ($class->attrGroups !== []) {
            return \true;
        }
        $magicGetMethod = $class->getMethod(MethodName::__GET);
        if ($magicGetMethod instanceof ClassMethod) {
            return \true;
        }
        foreach ($class->stmts as $stmt) {
            if ($stmt instanceof TraitUse) {
                return \true;
            }
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if ($classReflection instanceof ClassReflection) {
            $interfaces = $classReflection->getInterfaces();
            foreach ($interfaces as $interface) {
                if ($interface->hasNativeMethod(MethodName::CONSTRUCT)) {
                    return \true;
                }
            }
        }
        return $this->propertyWriteonlyAnalyzer->hasClassDynamicPropertyNames($class);
    }
}
