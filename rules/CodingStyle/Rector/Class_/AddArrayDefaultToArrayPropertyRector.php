<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\Type\Type;
use Rector\CodingStyle\TypeAnalyzer\IterableTypeAnalyzer;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\AddArrayDefaultToArrayPropertyRectorTest
 * @changelog https://3v4l.org/dPlUg
 */
final class AddArrayDefaultToArrayPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\CodingStyle\TypeAnalyzer\IterableTypeAnalyzer
     */
    private $iterableTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(PropertyFetchAnalyzer $propertyFetchAnalyzer, IterableTypeAnalyzer $iterableTypeAnalyzer, ArgsAnalyzer $argsAnalyzer, VisibilityManipulator $visibilityManipulator)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->iterableTypeAnalyzer = $iterableTypeAnalyzer;
        $this->argsAnalyzer = $argsAnalyzer;
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Adds array default value to property to prevent foreach over null error', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    public function isEmpty()
    {
        return $this->values === null;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values = [];

    public function isEmpty()
    {
        return $this->values === [];
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
        if ($node->isReadonly()) {
            return null;
        }
        $changedProperties = $this->collectPropertyNamesWithMissingDefaultArray($node);
        if ($changedProperties === []) {
            return null;
        }
        $this->completeDefaultArrayToPropertyNames($node, $changedProperties);
        // $this->variable !== null && count($this->variable) > 0 → count($this->variable) > 0
        $this->clearNotNullBeforeCount($node, $changedProperties);
        // $this->variable === null → $this->variable === []
        $this->replaceNullComparisonOfArrayPropertiesWithArrayComparison($node, $changedProperties);
        return $node;
    }
    /**
     * @return string[]
     */
    private function collectPropertyNamesWithMissingDefaultArray(Class_ $class) : array
    {
        $propertyNames = [];
        $this->traverseNodesWithCallable($class, function (Node $node) use(&$propertyNames) {
            if (!$node instanceof PropertyProperty) {
                return null;
            }
            if ($node->default instanceof Expr) {
                return null;
            }
            $varType = $this->resolveVarType($node);
            if (!$this->iterableTypeAnalyzer->detect($varType)) {
                return null;
            }
            $property = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (!$property instanceof Property) {
                return null;
            }
            if ($this->visibilityManipulator->isReadonly($property)) {
                return null;
            }
            $propertyNames[] = $this->getName($node);
            return null;
        });
        return $propertyNames;
    }
    /**
     * @param string[] $propertyNames
     */
    private function completeDefaultArrayToPropertyNames(Class_ $class, array $propertyNames) : void
    {
        $this->traverseNodesWithCallable($class, function (Node $node) use($propertyNames) : ?PropertyProperty {
            if (!$node instanceof PropertyProperty) {
                return null;
            }
            if (!$this->isNames($node, $propertyNames)) {
                return null;
            }
            $node->default = new Array_();
            return $node;
        });
    }
    /**
     * @param string[] $propertyNames
     */
    private function clearNotNullBeforeCount(Class_ $class, array $propertyNames) : void
    {
        $this->traverseNodesWithCallable($class, function (Node $node) use($propertyNames) : ?Expr {
            if (!$node instanceof BooleanAnd) {
                return null;
            }
            if (!$this->isLocalPropertyOfNamesNotIdenticalToNull($node->left, $propertyNames)) {
                return null;
            }
            $isNextNodeCountingProperty = (bool) $this->betterNodeFinder->findFirst($node->right, function (Node $node) use($propertyNames) : bool {
                if (!$node instanceof FuncCall) {
                    return \false;
                }
                if (!$this->isName($node, 'count')) {
                    return \false;
                }
                if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($node->args, 0)) {
                    return \false;
                }
                /** @var Arg $firstArg */
                $firstArg = $node->args[0];
                $countedArgument = $firstArg->value;
                if (!$countedArgument instanceof PropertyFetch) {
                    return \false;
                }
                return $this->isNames($countedArgument, $propertyNames);
            });
            if (!$isNextNodeCountingProperty) {
                return null;
            }
            return $node->right;
        });
    }
    /**
     * @param string[] $propertyNames
     */
    private function replaceNullComparisonOfArrayPropertiesWithArrayComparison(Class_ $class, array $propertyNames) : void
    {
        // replace comparison to "null" with "[]"
        $this->traverseNodesWithCallable($class, function (Node $node) use($propertyNames) : ?BinaryOp {
            if (!$node instanceof BinaryOp) {
                return null;
            }
            if ($this->propertyFetchAnalyzer->isLocalPropertyOfNames($node->left, $propertyNames) && $this->valueResolver->isNull($node->right)) {
                $node->right = new Array_();
            }
            if ($this->propertyFetchAnalyzer->isLocalPropertyOfNames($node->right, $propertyNames) && $this->valueResolver->isNull($node->left)) {
                $node->left = new Array_();
            }
            return $node;
        });
    }
    private function resolveVarType(PropertyProperty $propertyProperty) : Type
    {
        /** @var Property $propertyNode */
        $propertyNode = $propertyProperty->getAttribute(AttributeKey::PARENT_NODE);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($propertyNode);
        return $phpDocInfo->getVarType();
    }
    /**
     * @param string[] $propertyNames
     */
    private function isLocalPropertyOfNamesNotIdenticalToNull(Expr $expr, array $propertyNames) : bool
    {
        if (!$expr instanceof NotIdentical) {
            return \false;
        }
        if ($this->propertyFetchAnalyzer->isLocalPropertyOfNames($expr->left, $propertyNames) && $this->valueResolver->isNull($expr->right)) {
            return \true;
        }
        if (!$this->propertyFetchAnalyzer->isLocalPropertyOfNames($expr->right, $propertyNames)) {
            return \false;
        }
        return $this->valueResolver->isNull($expr->left);
    }
}
