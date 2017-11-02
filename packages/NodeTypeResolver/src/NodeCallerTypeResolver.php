<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
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
        return $this->resolverMethodCallReturnTypes($node);

    }

    private function processStaticCallNode(StaticCall $staticCallNode): void
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

        $staticCallNode->setAttribute(Attribute::CALLER_TYPES, $types);
    }

    private function processMethodCallNode(MethodCall $methodCallNode): void
    {
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
            return;
        }

        $nodeTypes = (array) $callerNode->getAttribute(Attribute::TYPES);
        if ($nodeTypes) {
            $methodCallNode->setAttribute(Attribute::CALLER_TYPES, $nodeTypes);
        }

        $nodeTypes = (array) $callerNode->getAttribute(Attribute::CALLER_TYPES);
        $methodCallNode->setAttribute(Attribute::CALLER_TYPES, $nodeTypes);



//        if ($parentNode instanceof MethodCall && $parentNode->var instanceof MethodCall) {
//            // resolve return type type
//            // @todo: consider Attribute::RETURN_TYPES for MethodCall and StaticCall types
//
//            $nodeVarTypes = $parentNode->var->var->getAttribute(Attribute::TYPES);
//            $nodeVarType = array_shift($nodeVarTypes);
//
//            $methodName = $parentNode->var->name->toString(); // method
//            $methodReturnType = $this->methodReflector->getMethodReturnType($nodeVarType, $methodName);
//
//            if ($methodReturnType) {
//                return [$methodReturnType];
//            }
//        }
    }

    /**
     * Returnts on magic method calls, void type, scalar types and array types.

     * @return string[]
     */
    private function resolverMethodCallReturnTypes(MethodCall $node): array
    {
        if ($node->var instanceof MethodCall) {
            return $this->resolverMethodCallReturnTypes($node);
        }

        $callerNodeTypes = $node->var->getAttribute(Attribute::TYPES);
        $callerNodeType = array_shift($callerNodeTypes);

        $methodName = $node->name->toString();
        $callerReturnType = $this->methodReflector->getMethodReturnType($callerNodeType, $methodName);

        if ($callerReturnType) {
            return [$callerReturnType];
        }

        return [];
    }
}
