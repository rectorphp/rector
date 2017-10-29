<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
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
        if (! $node instanceof ClassMethod) {
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
     * @param ClassMethod $classMethodNode
     */
    public function refactor(Node $classMethodNode): ?Node
    {
        /** @var Class_ $classMethodNode */
        $classNode = $classMethodNode->getAttribute(Attribute::CLASS_NODE);
        $classNodeTypes = $classNode->getAttribute(Attribute::TYPES);
        $matchingTypes = $this->getMatchingTypesForClassNode($classNodeTypes);

        $methodName = $classMethodNode->name->toString();

        foreach ($matchingTypes as $matchingType) {
            $configuration = $this->argumentsToRemoveByMethodAndClass[$matchingType];

            foreach ($configuration as $method => $argumentsToRemove) {
                if ($methodName === $method) {
                    return $this->processClassMethodNodeWithArgumentsToRemove($classMethodNode, $argumentsToRemove);
                }
            }
        }

        return $classMethodNode;
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
     * @param string[] $argumentsToRemove
     */
    private function processClassMethodNodeWithArgumentsToRemove(
        ClassMethod $classMethodNode,
        array $argumentsToRemove
    ): ClassMethod {
        /** @var Param $param */
        foreach ($classMethodNode->params as $key => $param) {
            $parameterName = $param->var->name;

            if (! in_array($parameterName, $argumentsToRemove)) {
                continue;
            }

            unset($classMethodNode->params[$key]);
        }

        return $classMethodNode;
    }
}
