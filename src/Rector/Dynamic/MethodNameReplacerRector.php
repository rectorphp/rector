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
     * @var string|null
     */
    private $activeType;

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
        $this->activeType = null;

        $matchedType = $this->methodCallAnalyzer->matchMethodCallTypes($node, $this->getClasses());
        if ($matchedType !== null) {
            $this->activeType = $matchedType;

            return true;
        }

        $matchedType = $this->staticMethodCallAnalyzer->matchStaticMethodCallTypes($node, $this->getClasses());
        if ($matchedType !== null) {
            $this->activeType = $matchedType;

            return true;
        }

        return false;
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $oldToNewMethods = $this->perClassOldToNewMethods[$this->activeType];

        $methodName = $node->name->name;

        if ($this->isClassRename($oldToNewMethods)) {
            [$newClass, $newMethod] = $oldToNewMethods[$methodName];

            $node->class = new Name($newClass);
            $node->name->name = $newMethod;
        } else { // is only method rename
            foreach ($oldToNewMethods as $oldMethod => $newMethod) {
                if ($methodName !== $oldMethod) {
                    continue;
                }

                $node->name->name = $newMethod;
            }
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
}
