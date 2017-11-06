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

/**
 * This will tell the type of Node, which is calling this method
 *
 * E.g.:
 * - {$this}->callMe()
 * - $this->{getThis()}->callMe()
 * - {new John}->callMe()
 * - {parent}::callMe()
 * - {$this}::callMe()
 * - {self}::callMe()
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
            return $this->resolveMethodCallReturnTypes($node);
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
    private function resolveMethodCallReturnTypes(MethodCall $node): array
    {
        if ($node->var instanceof MethodCall) {
            $parentReturnTypes = $this->resolveMethodCallReturnTypes($node->var);

            $methodName = $node->var->name->toString();

            $returnTypes = $this->methodReflector->resolveReturnTypesForTypesAndMethod($parentReturnTypes, $methodName);
            if ($returnTypes) {
                return $returnTypes;
            }
        }

        if ($node->var instanceof Variable || $node->var instanceof PropertyFetch) {
            return (array) $node->var->getAttribute(Attribute::TYPES);
        }

        /** @var string[] $callerNodeTypes */
        $callerNodeTypes = (array) $node->var->getAttribute(Attribute::TYPES);
        $methodName = $node->name->toString();

        return $this->methodReflector->resolveReturnTypesForTypesAndMethod($callerNodeTypes, $methodName);
    }
}
