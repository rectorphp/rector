<?php

declare(strict_types=1);

namespace Rector\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Commander\NodeRemovingCommander;
use Rector\PhpParser\Node\Manipulator\PropertyManipulator;

/**
 * @property NodeRemovingCommander $nodeRemovingCommander
 */
trait ComplexRemovalTrait
{
    /**
     * @var PropertyManipulator
     */
    private $propertyManipulator;

    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @required
     */
    public function autowireComplextRemovalTrait(
        PropertyManipulator $propertyManipulator,
        ParsedNodesByType $parsedNodesByType
    ): void {
        $this->parsedNodesByType = $parsedNodesByType;
        $this->propertyManipulator = $propertyManipulator;
    }

    abstract protected function removeNode(Node $node): void;

    protected function removeClassMethodAndUsages(ClassMethod $classMethod): void
    {
        $this->removeNode($classMethod);

        $classMethodCalls = $this->parsedNodesByType->findClassMethodCalls($classMethod);
        foreach ($classMethodCalls as $classMethodCall) {
            if ($classMethodCall instanceof Array_) {
                continue;
            }

            $this->removeMethodCall($classMethodCall);
        }
    }

    /**
     * @param string[] $classMethodNamesToSkip
     */
    protected function removePropertyAndUsages(
        PropertyProperty $propertyProperty,
        array $classMethodNamesToSkip = []
    ): void {
        $shouldKeepProperty = false;

        $propertyFetches = $this->propertyManipulator->getAllPropertyFetch($propertyProperty);
        foreach ($propertyFetches as $propertyFetch) {
            if ($this->shouldSkipPropertyForClassMethod($propertyFetch, $classMethodNamesToSkip)) {
                $shouldKeepProperty = true;
                continue;
            }

            $assign = $propertyFetch->getAttribute(AttributeKey::PARENT_NODE);

            while ($assign !== null && ! $assign instanceof Assign) {
                $assign = $assign->getAttribute(AttributeKey::PARENT_NODE);
            }

            if (! $assign instanceof Assign) {
                throw new ShouldNotHappenException("Can't handle this situation");
            }

            $this->removeAssignNode($assign);
        }

        if ($shouldKeepProperty) {
            return;
        }

        /** @var Property $property */
        $property = $propertyProperty->getAttribute(AttributeKey::PARENT_NODE);
        $this->removeNode($propertyProperty);

        foreach ($property->props as $prop) {
            if (! $this->nodeRemovingCommander->isNodeRemoved($prop)) {
                //if the property has at least one node left -> return
                return;
            }
        }

        $this->removeNode($property);
    }

    /**
     * @param Node|int|string|null $expr
     * @return Expr[]
     */
    protected function keepLivingCodeFromExpr($expr): array
    {
        if (! $expr instanceof Node ||
            $expr instanceof Closure ||
            $expr instanceof Name ||
            $expr instanceof Identifier ||
            $expr instanceof Scalar ||
            $expr instanceof ConstFetch) {
            return [];
        }
        if ($expr instanceof Cast ||
            $expr instanceof Empty_ ||
            $expr instanceof UnaryMinus ||
            $expr instanceof UnaryPlus ||
            $expr instanceof BitwiseNot ||
            $expr instanceof BooleanNot ||
            $expr instanceof Clone_) {
            return $this->keepLivingCodeFromExpr($expr->expr);
        }
        if ($expr instanceof Variable) {
            return $this->keepLivingCodeFromExpr($expr->name);
        }
        if ($expr instanceof PropertyFetch) {
            return array_merge(
                $this->keepLivingCodeFromExpr($expr->var),
                $this->keepLivingCodeFromExpr($expr->name)
            );
        }
        if ($expr instanceof ArrayDimFetch) {
            return array_merge(
                $this->keepLivingCodeFromExpr($expr->var),
                $this->keepLivingCodeFromExpr($expr->dim)
            );
        }
        if ($expr instanceof ClassConstFetch ||
            $expr instanceof StaticPropertyFetch) {
            return array_merge(
                $this->keepLivingCodeFromExpr($expr->class),
                $this->keepLivingCodeFromExpr($expr->name)
            );
        }
        if ($expr instanceof BinaryOp
            && ! (
                $expr instanceof LogicalAnd ||
                $expr instanceof BooleanAnd ||
                $expr instanceof LogicalOr ||
                $expr instanceof BooleanOr ||
                $expr instanceof Coalesce
            )) {
            return array_merge(
                $this->keepLivingCodeFromExpr($expr->left),
                $this->keepLivingCodeFromExpr($expr->right)
            );
        }
        if ($expr instanceof Instanceof_) {
            return array_merge(
                $this->keepLivingCodeFromExpr($expr->expr),
                $this->keepLivingCodeFromExpr($expr->class)
            );
        }

        if ($expr instanceof Isset_) {
            return array_merge(...array_map([$this, 'keepLivingCodeFromExpr'], $expr->vars));
        }

        return [$expr];
    }

    private function removeAssignNode(Assign $assign): void
    {
        $currentStatement = $assign->getAttribute(AttributeKey::CURRENT_STATEMENT);
        $this->addLivingCodeBeforeNode($assign->var, $currentStatement);

        /** @var Assign $assign */
        $parent = $assign->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Expression) {
            $this->addLivingCodeBeforeNode($assign->expr, $currentStatement);
            $this->removeNode($assign);
        } else {
            $this->replaceNode($assign, $assign->expr);
        }
    }

    private function addLivingCodeBeforeNode(Expr $expr, Node $addBeforeThisNode): void
    {
        foreach ($this->keepLivingCodeFromExpr($expr) as $expr) {
            $this->addNodeBeforeNode(new Expression($expr), $addBeforeThisNode);
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function removeMethodCall(Node $node): void
    {
        $currentStatement = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        foreach ($node->args as $arg) {
            $this->addLivingCodeBeforeNode($arg->value, $currentStatement);
        }
        $this->removeNode($node);
    }

    /**
     * @param string[] $classMethodNamesToSkip
     */
    private function shouldSkipPropertyForClassMethod(PropertyFetch $propertyFetch, array $classMethodNamesToSkip): bool
    {
        /** @var ClassMethod|null $methodNode */
        $classMethodNode = $propertyFetch->getAttribute(AttributeKey::METHOD_NODE);
        if ($classMethodNode === null) {
            return false;
        }

        $classMethodName = $this->getName($classMethodNode);
        if ($classMethodName === null) {
            return false;
        }

        return in_array($classMethodName, $classMethodNamesToSkip, true);
    }
}
