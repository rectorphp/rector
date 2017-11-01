<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
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
final class CallerTypeResolver extends NodeVisitorAbstract
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof StaticCall) {
            $this->processStaticCallNode($node);

            return null;
        }

        if ($node instanceof MethodCall) {
            $this->processMethodCallNode($node);

            return null;
        }

        return $node;
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
        $node = $this->betterNodeFinder->findFirstInstanceOfAny(
            $methodCallNode,
            [Variable::class, PropertyFetch::class]
        );

        $nodeTypes = (array) $node->getAttribute(Attribute::TYPES);
        if ($nodeTypes) {
            $methodCallNode->setAttribute(Attribute::CALLER_TYPES, $nodeTypes);
        }

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
}
