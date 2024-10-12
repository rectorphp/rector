<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\CodeQuality\NodeFactory\TypedPropertyFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Class_\DynamicDocBlockPropertyToNativePropertyRector\DynamicDocBlockPropertyToNativePropertyRectorTest
 */
final class DynamicDocBlockPropertyToNativePropertyRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeFactory\TypedPropertyFactory
     */
    private $typedPropertyFactory;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(PhpAttributeAnalyzer $phpAttributeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater, TypedPropertyFactory $typedPropertyFactory, TestsNodeAnalyzer $testsNodeAnalyzer, ValueResolver $valueResolver)
    {
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->typedPropertyFactory = $typedPropertyFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turn dynamic docblock properties on class with no parents to explicit ones', [new CodeSample(<<<'CODE_SAMPLE'
/**
 * @property SomeDependency $someDependency
 */
#[\AllowDynamicProperties]
final class SomeClass
{
    public function __construct()
    {
        $this->someDependency = new SomeDependency();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private SomeDependency $someDependency;

    public function __construct()
    {
        $this->someDependency = new SomeDependency();
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
        if (!$this->phpAttributeAnalyzer->hasPhpAttribute($node, 'AllowDynamicProperties')) {
            return null;
        }
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        // 2. add defined @property explicitly
        $classPhpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$classPhpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $propertyPhpDocTagNodes = $classPhpDocInfo->getTagsByName('property');
        if ($propertyPhpDocTagNodes === []) {
            return null;
        }
        // 1. remove dynamic attribute, most likely any
        foreach ($node->attrGroups as $key => $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === 'AllowDynamicProperties') {
                    unset($node->attrGroups[$key]);
                    continue 2;
                }
            }
        }
        $node->attrGroups = \array_values($node->attrGroups);
        $newProperties = $this->createNewPropertyFromPropertyTagValueNodes($propertyPhpDocTagNodes, $node);
        // remove property tags
        foreach ($propertyPhpDocTagNodes as $propertyPhpDocTagNode) {
            // remove from docblock
            $this->phpDocTagRemover->removeTagValueFromNode($classPhpDocInfo, $propertyPhpDocTagNode);
        }
        // merge new properties to start of the file
        $node->stmts = \array_merge($newProperties, $node->stmts);
        // update doc info
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_DYNAMIC_PROPERTIES;
    }
    /**
     * @param PhpDocTagNode[] $propertyPhpDocTagNodes
     * @return Property[]
     */
    private function createNewPropertyFromPropertyTagValueNodes(array $propertyPhpDocTagNodes, Class_ $class) : array
    {
        $newProperties = [];
        foreach ($propertyPhpDocTagNodes as $propertyPhpDocTagNode) {
            // add explicit native property
            $propertyTagValueNode = $propertyPhpDocTagNode->value;
            if (!$propertyTagValueNode instanceof PropertyTagValueNode) {
                continue;
            }
            $propertyName = \ltrim($propertyTagValueNode->propertyName, '$');
            if ($this->isPromotedProperty($class, $propertyName)) {
                continue;
            }
            // is property already defined?
            if ($class->getProperty($propertyName)) {
                // improve exising one type if needed
                $existingProperty = $class->getProperty($propertyName);
                if ($existingProperty->type !== null) {
                    continue;
                }
                $defaultValue = $existingProperty->props[0]->default;
                $isNullable = $defaultValue instanceof Expr && $this->valueResolver->isNull($defaultValue);
                $existingProperty->type = $this->typedPropertyFactory->createPropertyTypeNode($propertyTagValueNode, $class, $isNullable);
                continue;
            }
            $newProperties[] = $this->typedPropertyFactory->createFromPropertyTagValueNode($propertyTagValueNode, $class, $propertyName);
        }
        return $newProperties;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        // skip magic
        $getClassMethod = $class->getMethod('__get');
        if ($getClassMethod instanceof ClassMethod) {
            return \true;
        }
        $setClassMethod = $class->getMethod('__set');
        if ($setClassMethod instanceof ClassMethod) {
            return \true;
        }
        if (!$class->extends instanceof Node) {
            return \false;
        }
        return !$this->testsNodeAnalyzer->isInTestClass($class);
    }
    private function isPromotedProperty(Class_ $class, string $propertyName) : bool
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod instanceof ClassMethod) {
            foreach ($constructClassMethod->params as $param) {
                if (!$param->flags) {
                    continue;
                }
                $paramName = $this->getName($param->var);
                if ($paramName === $propertyName) {
                    return \true;
                }
            }
        }
        return \false;
    }
}
