<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitor;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\DeadCode\NodeAnalyzer\PropertyWriteonlyAnalyzer;
use Rector\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\RemoveUnusedPrivatePropertyRectorTest
 */
final class RemoveUnusedPrivatePropertyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PropertyFetchFinder $propertyFetchFinder;
    /**
     * @readonly
     */
    private PropertyWriteonlyAnalyzer $propertyWriteonlyAnalyzer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(PropertyFetchFinder $propertyFetchFinder, PropertyWriteonlyAnalyzer $propertyWriteonlyAnalyzer, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->propertyWriteonlyAnalyzer = $propertyWriteonlyAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused private properties', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private $property;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
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
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Property) {
                continue;
            }
            if ($this->shouldSkipProperty($stmt)) {
                continue;
            }
            if (!$this->shouldRemoveProperty($node, $stmt)) {
                continue;
            }
            // remove property
            unset($node->stmts[$key]);
            $propertyName = $this->getName($stmt);
            $this->removePropertyAssigns($node, $propertyName);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkipProperty(Property $property) : bool
    {
        // has some attribute logic
        if ($property->attrGroups !== []) {
            return \true;
        }
        if (\count($property->props) !== 1) {
            return \true;
        }
        if (!$property->isPrivate()) {
            return \true;
        }
        // has some possible magic
        if ($property->isStatic()) {
            return \true;
        }
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if (!$propertyPhpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        // skip as might contain important metadata
        return $propertyPhpDocInfo->hasByType(DoctrineAnnotationTagValueNode::class);
    }
    private function shouldRemoveProperty(Class_ $class, Property $property) : bool
    {
        $propertyName = $this->getName($property);
        $propertyFetches = $this->propertyFetchFinder->findLocalPropertyFetchesByName($class, $propertyName);
        if ($propertyFetches === []) {
            return \true;
        }
        return $this->propertyWriteonlyAnalyzer->arePropertyFetchesExclusivelyBeingAssignedTo($propertyFetches);
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        foreach ($class->stmts as $stmt) {
            // unclear what property can be used there
            if ($stmt instanceof TraitUse) {
                return \true;
            }
        }
        return $this->propertyWriteonlyAnalyzer->hasClassDynamicPropertyNames($class);
    }
    private function removePropertyAssigns(Class_ $class, string $propertyName) : void
    {
        $this->traverseNodesWithCallable($class, function (Node $node) use($class, $propertyName) {
            if (!$node instanceof Expression && !$node instanceof Return_) {
                return null;
            }
            if (!$node->expr instanceof Assign) {
                return null;
            }
            $assign = $node->expr;
            if (!$this->propertyFetchFinder->isLocalPropertyFetchByName($assign->var, $class, $propertyName)) {
                return null;
            }
            if ($node instanceof Expression) {
                return NodeVisitor::REMOVE_NODE;
            }
            $node->expr = $node->expr->expr;
            return $node;
        });
    }
}
