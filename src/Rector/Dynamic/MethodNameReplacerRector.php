<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
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
     * @param string[][]
     */
    public function __construct(
        array $perClassOldToNewMethods,
        MethodCallAnalyzer $methodCallAnalyzer,
        StaticMethodCallAnalyzer $staticMethodCallAnalyzer
    ) {
        $this->perClassOldToNewMethods = $perClassOldToNewMethods;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->staticMethodCallAnalyzer = $staticMethodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeTypes = null;

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

        return false;
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $oldToNewMethods = $this->matchOldToNewMethos();
        $methodName = $node->name->name;

        if (! isset($oldToNewMethods[$methodName])) {
            return $node;
        }

        if ($this->isClassRename($oldToNewMethods)) {
            [$newClass, $newMethod] = $oldToNewMethods[$methodName];

            $node->class = new Name($newClass);
            $node->name->name = $newMethod;

            return $node;
        }

        // is only method rename
        foreach ($oldToNewMethods as $oldMethod => $newMethod) {
            if ($methodName !== $oldMethod) {
                continue;
            }

            $node->name->name = $newMethod;
        }

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
}
