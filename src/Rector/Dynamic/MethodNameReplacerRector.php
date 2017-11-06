<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeAnalyzer\MethodNameAnalyzer;
use Rector\NodeAnalyzer\StaticMethodCallAnalyzer;
use Rector\Rector\AbstractRector;

final class MethodNameReplacerRector extends AbstractRector
{
    /**
     * class => [
     *     oldMethod => newMethod
     * ]
     *
     * or (typically for static calls):
     *
     * class => [
     *     oldMethod => [
     *          newClass, newMethod
     *     ]
     * ]
     *
     * @todo consider splitting to static call replacer or class rename,
     * this api can lead users to bugs (already did)
     *
     * @var string[][]
     */
    private $perClassOldToNewMethods = [];

    /**
     * @var string[]
     */
    private $activeTypes = [];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var StaticMethodCallAnalyzer
     */
    private $staticMethodCallAnalyzer;

    /**
     * @var MethodNameAnalyzer
     */
    private $methodNameAnalyzer;

    /**
     * @param string[][]
     */
    public function __construct(
        array $perClassOldToNewMethods,
        MethodCallAnalyzer $methodCallAnalyzer,
        StaticMethodCallAnalyzer $staticMethodCallAnalyzer,
        MethodNameAnalyzer $methodNameAnalyzer
    ) {
        $this->perClassOldToNewMethods = $perClassOldToNewMethods;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->staticMethodCallAnalyzer = $staticMethodCallAnalyzer;
        $this->methodNameAnalyzer = $methodNameAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeTypes = [];

        $matchedTypes = $this->methodCallAnalyzer->matchTypes($node, $this->getClasses());
        if ($matchedTypes) {
            $this->activeTypes = $matchedTypes;

            return true;
        }

        $matchedTypes = $this->staticMethodCallAnalyzer->matchTypes($node, $this->getClasses());
        if ($matchedTypes) {
            $this->activeTypes = $matchedTypes;

            return true;
        }

        if ($this->isMethodName($node, $this->getClasses())) {
            return true;
        }

        return false;
    }

    /**
     * @param Identifier|StaticCall|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $oldToNewMethods = $this->matchOldToNewMethos();

        if ($node instanceof Identifier) {
            return $this->resolveIdentifier($node);
        }

        $methodName = $node->name->name;
        if (! isset($oldToNewMethods[$methodName])) {
            return $node;
        }

        if ($this->isClassRename($oldToNewMethods)) {
            return $this->resolveClassRename($node, $oldToNewMethods, $methodName);
        }

        $node->name->name = $oldToNewMethods[$methodName];

        return $node;
    }

    /**
     * @return string[]
     */
    private function getClasses(): array
    {
        return array_keys($this->perClassOldToNewMethods);
    }

    /**
     * @param mixed[] $oldToNewMethods
     */
    private function isClassRename(array $oldToNewMethods): bool
    {
        $firstMethodConfiguration = current($oldToNewMethods);

        return is_array($firstMethodConfiguration);
    }

    /**
     * @return string[]
     */
    private function matchOldToNewMethos(): array
    {
        foreach ($this->activeTypes as $activeType) {
            if ($this->perClassOldToNewMethods[$activeType]) {
                return $this->perClassOldToNewMethods[$activeType];
            }
        }

        return [];
    }

    /**
     * @param string[] $types
     */
    private function isMethodName(Node $node, array $types): bool
    {
        if (! $this->methodNameAnalyzer->isOverrideOfTypes($node, $types)) {
            return false;
        }

        /** @var Identifier $node */
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);

        /** @var Identifier $node */
        if (! isset($this->perClassOldToNewMethods[$parentClassName][$node->toString()])) {
            return false;
        }

        $this->activeTypes = [$parentClassName];

        return true;
    }

    private function resolveIdentifier(Identifier $node): Node
    {
        $oldToNewMethods = $this->matchOldToNewMethos();

        $methodName = $node->name;
        if (! isset($oldToNewMethods[$methodName])) {
            return $node;
        }

        $node->name = $oldToNewMethods[$methodName];

        return $node;
    }

    /**
     * @param StaticCall|MethodCall $node
     * @param string[] $oldToNewMethods
     */
    private function resolveClassRename(Node $node, array $oldToNewMethods, string $methodName): Node
    {
        [$newClass, $newMethod] = $oldToNewMethods[$methodName];

        $node->class = new Name($newClass);
        $node->name->name = $newMethod;

        return $node;
    }
}
