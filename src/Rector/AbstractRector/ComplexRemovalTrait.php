<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Commander\NodeRemovingCommander;
use Rector\Core\PhpParser\Node\Manipulator\PropertyManipulator;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\DeadCode\NodeManipulator\LivingCodeManipulator;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
use Rector\NodeCollector\NodeFinder\FunctionLikeParsedNodesFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * Located in another trait ↓
 * @property NodeRemovingCommander $nodeRemovingCommander
 * @property FunctionLikeParsedNodesFinder $functionLikeParsedNodesFinder
 */
trait ComplexRemovalTrait
{
    /**
     * @var ParsedNodeCollector
     */
    protected $parsedNodeCollector;

    /**
     * @var LivingCodeManipulator
     */
    protected $livingCodeManipulator;

    /**
     * @var BetterStandardPrinter
     */
    protected $betterStandardPrinter;

    /**
     * @var PropertyManipulator
     */
    private $propertyManipulator;

    /**
     * @required
     */
    public function autowireComplextRemovalTrait(
        PropertyManipulator $propertyManipulator,
        ParsedNodeCollector $parsedNodeCollector,
        LivingCodeManipulator $livingCodeManipulator,
        BetterStandardPrinter $betterStandardPrinter
    ): void {
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->propertyManipulator = $propertyManipulator;
        $this->livingCodeManipulator = $livingCodeManipulator;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    abstract protected function removeNode(Node $node): void;

    protected function removeClassMethodAndUsages(ClassMethod $classMethod): void
    {
        $this->removeNode($classMethod);

        $classMethodCalls = $this->functionLikeParsedNodesFinder->findClassMethodCalls($classMethod);
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

            // remove assigns
            $assign = $this->resolveAssign($propertyFetch);
            $this->removeAssignNode($assign);

            $this->removeConstructorDependency($assign);
        }

        if ($shouldKeepProperty) {
            return;
        }

        // remove __contruct param

        /** @var Property $property */
        $property = $propertyProperty->getAttribute(AttributeKey::PARENT_NODE);
        $this->removeNode($propertyProperty);

        foreach ($property->props as $prop) {
            if (! $this->nodeRemovingCommander->isNodeRemoved($prop)) {
                // if the property has at least one node left -> return
                return;
            }
        }

        $this->removeNode($property);
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
        foreach ($this->livingCodeManipulator->keepLivingCodeFromExpr($expr) as $expr) {
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
     * @param StaticPropertyFetch|PropertyFetch $expr
     * @param string[] $classMethodNamesToSkip
     */
    private function shouldSkipPropertyForClassMethod(Expr $expr, array $classMethodNamesToSkip): bool
    {
        /** @var ClassMethod|null $classMethodNode */
        $classMethodNode = $expr->getAttribute(AttributeKey::METHOD_NODE);
        if ($classMethodNode === null) {
            return false;
        }

        $classMethodName = $this->getName($classMethodNode);
        if ($classMethodName === null) {
            return false;
        }

        return in_array($classMethodName, $classMethodNamesToSkip, true);
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $expr
     */
    private function resolveAssign(Expr $expr): Assign
    {
        $assign = $expr->getAttribute(AttributeKey::PARENT_NODE);

        while ($assign !== null && ! $assign instanceof Assign) {
            $assign = $assign->getAttribute(AttributeKey::PARENT_NODE);
        }

        if (! $assign instanceof Assign) {
            throw new ShouldNotHappenException("Can't handle this situation");
        }

        return $assign;
    }

    private function removeConstructorDependency(Assign $assign): void
    {
        $methodName = $assign->getAttribute(AttributeKey::METHOD_NAME);
        if ($methodName !== '__construct') {
            return;
        }

        $class = $assign->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return;
        }

        /** @var Class_|null $class */
        $constructClassMethod = $class->getMethod('__construct');
        if ($constructClassMethod === null) {
            return;
        }

        foreach ($constructClassMethod->getParams() as $param) {
            if (! $this->betterStandardPrinter->areNodesEqual($param->var, $assign->expr)) {
                continue;
            }

            $this->removeNode($param);
        }
    }
}
