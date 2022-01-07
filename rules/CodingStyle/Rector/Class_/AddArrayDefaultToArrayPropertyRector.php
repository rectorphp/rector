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
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\AddArrayDefaultToArrayPropertyRectorTest
 * @see https://3v4l.org/dPlUg
 */
final class AddArrayDefaultToArrayPropertyRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \Rector\CodingStyle\TypeAnalyzer\IterableTypeAnalyzer $iterableTypeAnalyzer, \Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->iterableTypeAnalyzer = $iterableTypeAnalyzer;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Adds array default value to property to prevent foreach over null error', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
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
    private function collectPropertyNamesWithMissingDefaultArray(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $propertyNames = [];
        $this->traverseNodesWithCallable($class, function (\PhpParser\Node $node) use(&$propertyNames) {
            if (!$node instanceof \PhpParser\Node\Stmt\PropertyProperty) {
                return null;
            }
            if ($node->default !== null) {
                return null;
            }
            $varType = $this->resolveVarType($node);
            if (!$this->iterableTypeAnalyzer->detect($varType)) {
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
    private function completeDefaultArrayToPropertyNames(\PhpParser\Node\Stmt\Class_ $class, array $propertyNames) : void
    {
        $this->traverseNodesWithCallable($class, function (\PhpParser\Node $class) use($propertyNames) : ?PropertyProperty {
            if (!$class instanceof \PhpParser\Node\Stmt\PropertyProperty) {
                return null;
            }
            if (!$this->isNames($class, $propertyNames)) {
                return null;
            }
            $class->default = new \PhpParser\Node\Expr\Array_();
            return $class;
        });
    }
    /**
     * @param string[] $propertyNames
     */
    private function clearNotNullBeforeCount(\PhpParser\Node\Stmt\Class_ $class, array $propertyNames) : void
    {
        $this->traverseNodesWithCallable($class, function (\PhpParser\Node $node) use($propertyNames) : ?Expr {
            if (!$node instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd) {
                return null;
            }
            if (!$this->isLocalPropertyOfNamesNotIdenticalToNull($node->left, $propertyNames)) {
                return null;
            }
            $isNextNodeCountingProperty = (bool) $this->betterNodeFinder->findFirst($node->right, function (\PhpParser\Node $node) use($propertyNames) : bool {
                if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
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
                if (!$countedArgument instanceof \PhpParser\Node\Expr\PropertyFetch) {
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
    private function replaceNullComparisonOfArrayPropertiesWithArrayComparison(\PhpParser\Node\Stmt\Class_ $class, array $propertyNames) : void
    {
        // replace comparison to "null" with "[]"
        $this->traverseNodesWithCallable($class, function (\PhpParser\Node $node) use($propertyNames) : ?BinaryOp {
            if (!$node instanceof \PhpParser\Node\Expr\BinaryOp) {
                return null;
            }
            if ($this->propertyFetchAnalyzer->isLocalPropertyOfNames($node->left, $propertyNames) && $this->valueResolver->isNull($node->right)) {
                $node->right = new \PhpParser\Node\Expr\Array_();
            }
            if ($this->propertyFetchAnalyzer->isLocalPropertyOfNames($node->right, $propertyNames) && $this->valueResolver->isNull($node->left)) {
                $node->left = new \PhpParser\Node\Expr\Array_();
            }
            return $node;
        });
    }
    private function resolveVarType(\PhpParser\Node\Stmt\PropertyProperty $propertyProperty) : \PHPStan\Type\Type
    {
        /** @var Property $property */
        $property = $propertyProperty->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        return $phpDocInfo->getVarType();
    }
    /**
     * @param string[] $propertyNames
     */
    private function isLocalPropertyOfNamesNotIdenticalToNull(\PhpParser\Node\Expr $expr, array $propertyNames) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical) {
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
