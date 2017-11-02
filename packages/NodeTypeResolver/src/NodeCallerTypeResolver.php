<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use Rector\BetterReflection\Reflector\MethodReflector;
use Rector\Node\Attribute;

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
     * @var MethodReflector
     */
    private $methodReflector;

    public function __construct(MethodReflector $methodReflector)
    {
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
     *
     * @return string[]
     */
    private function resolverMethodCallReturnTypes(MethodCall $node): array
    {
        if ($node->var instanceof MethodCall) {
            return $this->resolverMethodCallReturnTypes($node->var);
        }

        if ($node->var instanceof Variable) {
            return $node->var->getAttribute(Attribute::TYPES);
        }

        /** @var string[]|null $callerNodeTypes */
        $callerNodeTypes = $node->var->getAttribute(Attribute::TYPES);
        $callerNodeType = $callerNodeTypes[0] ?? null;

        $methodName = $node->name->toString();

        if (! $callerNodeType || ! $methodName) {
            return [];
        }

        $callerReturnType = $this->methodReflector->getMethodReturnType($callerNodeType, $methodName);
        if ($callerReturnType) {
            if ($callerReturnType === 'self') {
                return $callerNodeTypes;
            }

            return [$callerReturnType];
        }

        return [];
    }
}
