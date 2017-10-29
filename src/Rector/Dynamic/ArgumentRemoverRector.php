<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;

/**
 * Remove arguments, that is used no more.
 *
 * Before:
 * - $this->callMe($one, $two);
 *
 * After:
 * - $this->callMe($one);
 */
final class ArgumentRemoverRector extends AbstractRector
{
    /**
     * class => [
     *      method => [
     *          argument,
     *              anotherArgument
     *      ]
     * ]
     *
     * @var string[]
     */
    private $argumentsToRemoveByMethodAndClass = [];

    /**
     * @param mixed[] $argumentsToRemoveByMethodAndClass
     */
    public function __construct(array $argumentsToRemoveByMethodAndClass)
    {
        $this->argumentsToRemoveByMethodAndClass = $argumentsToRemoveByMethodAndClass;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof ClassMethod && ! $node instanceof MethodCall && ! $node instanceof StaticCall) {
            return false;
        }

        /** @var ClassLike $classNode */
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        $classNodeTypes = $classNode->getAttribute(Attribute::TYPES);

        if (! $classNodeTypes) {
            return false;
        }

        return $this->isTypeMatch($classNodeTypes);
    }

    /**
     * @param ClassMethod|StaticCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var Class_ $node */
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        $classNodeTypes = $classNode->getAttribute(Attribute::TYPES);
        $matchingTypes = $this->getMatchingTypesForClassNode($classNodeTypes);

        if ($node instanceof ClassMethod) {
            return $this->processClassMethod($node, $matchingTypes);
        }

        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            return $this->processMethodAndStaticCall($node, $matchingTypes);
        }

        return $node;
    }

    /**
     * @return string[]
     */
    private function getClasses(): array
    {
        return array_keys($this->argumentsToRemoveByMethodAndClass);
    }

    /**
     * @param string[] $types
     */
    private function isTypeMatch(array $types): bool
    {
        return (bool) $this->getMatchingTypesForClassNode($types);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    private function getMatchingTypesForClassNode(array $types): array
    {
        return array_intersect($types, $this->getClasses());
    }

    /**
     * @param StaticCall|MethodCall $node
     * @param string[] $matchingTypes
     * @return StaticCall|MethodCall
     */
    private function processMethodAndStaticCall(Node $node, array $matchingTypes): Node
    {
        $methodName = $node->name->toString();

        foreach ($matchingTypes as $matchingType) {
            $configuration = $this->argumentsToRemoveByMethodAndClass[$matchingType];

            foreach ($configuration as $method => $argumentsToRemove) {
                if ($methodName === $method) {
                    return $this->removeParameterFromMethodAndStaticCall($node, $argumentsToRemove);
                }
            }
        }
    }

    /**
     * @param string[] $matchingTypes
     */
    private function processClassMethod(ClassMethod $classMethodNode, array $matchingTypes): ClassMethod
    {
        $methodName = $classMethodNode->name->toString();

        foreach ($matchingTypes as $matchingType) {
            $configuration = $this->argumentsToRemoveByMethodAndClass[$matchingType];

            foreach ($configuration as $method => $argumentsToRemove) {
                if ($methodName === $method) {
                    return $this->removeArgumentsFromClassMethod($classMethodNode, $argumentsToRemove);
                }
            }
        }
    }

    /**
     * @param string[] $argumentsToRemove
     */
    private function removeArgumentsFromClassMethod(ClassMethod $classMethodNode, array $argumentsToRemove): ClassMethod
    {
        /** @var Param $param */
        foreach ($classMethodNode->params as $key => $param) {
            $parameterName = $param->var->name;
            if (! in_array($parameterName, $argumentsToRemove, true)) {
                continue;
            }

            unset($classMethodNode->params[$key]);
        }

        return $classMethodNode;
    }

    /**
     * @param StaticCall|MethodCall $node
     * @param string[] $argumentsToRemove
     */
    private function removeParameterFromMethodAndStaticCall(Node $node, array $argumentsToRemove): Node
    {
        /** @var Arg $arg */
        foreach ($node->args as $key => $arg) {
            $argumentName = $arg->value->name;
            if (! in_array($argumentName, $argumentsToRemove, true)) {
                continue;
            }

            unset($node->args[$key]);
        }

        return $node;
    }
}
