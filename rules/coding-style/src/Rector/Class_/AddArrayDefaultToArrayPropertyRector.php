<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Class_;

use PhpParser\Node;
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
use Rector\Core\NodeManipulator\PropertyFetchManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector\AddArrayDefaultToArrayPropertyRectorTest
 * @see https://3v4l.org/dPlUg
 */
final class AddArrayDefaultToArrayPropertyRector extends AbstractRector
{
    /**
     * @var PropertyFetchManipulator
     */
    private $propertyFetchManipulator;

    /**
     * @var IterableTypeAnalyzer
     */
    private $iterableTypeAnalyzer;

    public function __construct(
        PropertyFetchManipulator $propertyFetchManipulator,
        IterableTypeAnalyzer $iterableTypeAnalyzer
    ) {
        $this->propertyFetchManipulator = $propertyFetchManipulator;
        $this->iterableTypeAnalyzer = $iterableTypeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Adds array default value to property to prevent foreach over null error',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
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
    private function collectPropertyNamesWithMissingDefaultArray(Class_ $class): array
    {
        $propertyNames = [];
        $this->traverseNodesWithCallable($class, function (Node $node) use (&$propertyNames) {
            if (! $node instanceof PropertyProperty) {
                return null;
            }

            if ($node->default !== null) {
                return null;
            }

            $varType = $this->resolveVarType($node);
            if (! $this->iterableTypeAnalyzer->detect($varType)) {
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
    private function completeDefaultArrayToPropertyNames(Class_ $class, array $propertyNames): void
    {
        $this->traverseNodesWithCallable($class, function (Node $class) use ($propertyNames): ?PropertyProperty {
            if (! $class instanceof PropertyProperty) {
                return null;
            }

            if (! $this->isNames($class, $propertyNames)) {
                return null;
            }

            $class->default = new Array_();

            return $class;
        });
    }

    /**
     * @param string[] $propertyNames
     */
    private function clearNotNullBeforeCount(Class_ $class, array $propertyNames): void
    {
        $this->traverseNodesWithCallable($class, function (Node $node) use ($propertyNames): ?Expr {
            if (! $node instanceof BooleanAnd) {
                return null;
            }
            if (! $this->isLocalPropertyOfNamesNotIdenticalToNull($node->left, $propertyNames)) {
                return null;
            }

            $isNextNodeCountingProperty = (bool) $this->betterNodeFinder->findFirst($node->right, function (Node $node) use (
                $propertyNames
            ): ?bool {
                if (! $node instanceof FuncCall) {
                    return null;
                }

                if (! $this->isName($node, 'count')) {
                    return null;
                }

                if (! isset($node->args[0])) {
                    return null;
                }

                $countedArgument = $node->args[0]->value;
                if (! $countedArgument instanceof PropertyFetch) {
                    return null;
                }

                return $this->isNames($countedArgument, $propertyNames);
            });

            if (! $isNextNodeCountingProperty) {
                return null;
            }

            return $node->right;
        });
    }

    /**
     * @param string[] $propertyNames
     */
    private function replaceNullComparisonOfArrayPropertiesWithArrayComparison(
        Class_ $class,
        array $propertyNames
    ): void {
        // replace comparison to "null" with "[]"
        $this->traverseNodesWithCallable($class, function (Node $node) use ($propertyNames): ?BinaryOp {
            if (! $node instanceof BinaryOp) {
                return null;
            }

            if ($this->propertyFetchManipulator->isLocalPropertyOfNames(
                $node->left,
                $propertyNames
            ) && $this->valueResolver->isNull($node->right)) {
                $node->right = new Array_();
            }

            if ($this->propertyFetchManipulator->isLocalPropertyOfNames(
                $node->right,
                $propertyNames
            ) && $this->valueResolver->isNull($node->left)) {
                $node->left = new Array_();
            }

            return $node;
        });
    }

    private function resolveVarType(PropertyProperty $propertyProperty): Type
    {
        /** @var Property $property */
        $property = $propertyProperty->getAttribute(AttributeKey::PARENT_NODE);

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        return $phpDocInfo->getVarType();
    }

    /**
     * @param string[] $propertyNames
     */
    private function isLocalPropertyOfNamesNotIdenticalToNull(Expr $expr, array $propertyNames): bool
    {
        if (! $expr instanceof NotIdentical) {
            return false;
        }

        if ($this->propertyFetchManipulator->isLocalPropertyOfNames(
            $expr->left,
            $propertyNames
        ) && $this->valueResolver->isNull($expr->right)) {
            return true;
        }
        if (! $this->propertyFetchManipulator->isLocalPropertyOfNames($expr->right, $propertyNames)) {
            return false;
        }
        return $this->valueResolver->isNull($expr->left);
    }
}
