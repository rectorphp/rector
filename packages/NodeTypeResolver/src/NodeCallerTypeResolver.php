<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use Rector\BetterReflection\Reflector\MethodReflector;
use Rector\Node\Attribute;
use Rector\NodeTraverserQueue\BetterNodeFinder;

/**
 * This will tell the type of Node, which is calling this method
 *
 * E.g.:
 * - {$this}->callMe()
 * - $this->{getThis()}->callMe()
 * - {new John}->callMe()
 */
final class NodeCallerTypeResolver
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var MethodReflector
     */
    private $methodReflector;

    public function __construct(BetterNodeFinder $betterNodeFinder, MethodReflector $methodReflector)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->methodReflector = $methodReflector;
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        if ($node instanceof MethodCall) {
            return $this->resolverMethodCallReturnTypes($node);
        }

        if ($node instanceof StaticCall) {
            return $this->processStaticCallNode($node);
        }

        return [];
    }

    /**
     * @return string[]
     */
    private function processStaticCallNode(StaticCall $staticCallNode): array
    {
        $types = [];
        if ($staticCallNode->class instanceof Name) {
            $class = $staticCallNode->class->toString();
            if ($class === 'parent') {
                $types[] = $staticCallNode->class->getAttribute(Attribute::PARENT_CLASS_NAME);
            } else {
                $types[] = $class;
            }
        }

        if ($staticCallNode->class instanceof Variable) {
            $types[] = $staticCallNode->class->getAttribute(Attribute::CLASS_NAME);
        }

        return $types;
    }

    /**
     * Returnts on magic method calls, void type, scalar types and array types.

     * @return string[]
     */
    private function resolverMethodCallReturnTypes(MethodCall $node): array
    {
        if ($node->var instanceof MethodCall) {
            return $this->resolverMethodCallReturnTypes($node->var);
        }

        $callerNodeTypes = $node->var->getAttribute(Attribute::TYPES);
        $callerNodeType = $callerNodeTypes[0] ?? [];

        $methodName = $node->name->toString();
        $callerReturnType = $this->methodReflector->getMethodReturnType($callerNodeType, $methodName);
        if ($callerReturnType) {
            if ($callerReturnType === 'self') {
                return $callerNodeTypes;
            }

            return [$callerReturnType];
        }

        return [];
    }

    /**
     * @return string[]
     */
    private function processMethodCallNode(MethodCall $methodCallNode): array
    {
        // @todo extract to own method
        $callerNode = $this->betterNodeFinder->findFirst(
            $methodCallNode,
            function (Node $node) use ($methodCallNode) {
                if ($node instanceof Variable || $node instanceof PropertyFetch) {
                    return $node;
                }

                if ($node instanceof MethodCall) {
                    $returnTypes = $this->resolverMethodCallReturnTypes($node);
                    if ($returnTypes) {
                        $node->setAttribute(Attribute::CALLER_TYPES, $returnTypes);
                    }

                    return $node;
                    // 2 options:
                    // 1. self - fluent interface, first variable
                    // 2. another object
                }

                if ($node === $methodCallNode) {
                    return null;
                }
            }
        );

        if ($callerNode === null) {
            return [];
        }

        $nodeTypes = (array) $callerNode->getAttribute(Attribute::TYPES);
        if ($nodeTypes) {
            return $nodeTypes;
        }

        return (array) $callerNode->getAttribute(Attribute::CALLER_TYPES);
    }
}
