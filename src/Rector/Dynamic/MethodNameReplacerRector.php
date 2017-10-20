<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use Nette\Utils\Arrays;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Node\Attribute;
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
     * @param string[][]
     */
    public function __construct(array $perClassOldToNewMethods)
    {
        $this->perClassOldToNewMethods = $perClassOldToNewMethods;
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeType = null;

        if ($this->isOnTypeCall($node)) {
            return true;
        }

        if ($this->isStaticCallOnType($node)) {
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

        if ($this->isClassRename($oldToNewMethods)) {

        } else { // is only method rename
            foreach ($oldToNewMethods as $oldMethod => $newMethod) {
                $methodName = $node->name->name;
                if ($methodName !== $oldMethod) {
                    continue;
                }

                $node->name->name = $newMethod;
            }
        }

        return $node;
    }

    /**
     * @todo use method analyzer instead
     */
    private function isOnTypeCall(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof Variable) {
            return false;
        }

        /** @var string|null $type */
        $type = $node->var->getAttribute(Attribute::TYPE);
        if ($type === null) {
            return false;
        }

        if (! $this->isTypeRelevant($type)) {
            return false;
        }

        $this->activeType = $type;

        return true;
    }

    /**
     * @todo use method analyzer instead
     */
    private function isStaticCallOnType(Node $node): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
            return false;
        }

        if (! $node->class instanceof Name) {
            return false;
        }

        $type = $node->class->toString();

        if (! $this->isTypeRelevant($type)) {
            return false;
        }

        $this->activeType = $type;

        return true;
    }

    private function isTypeRelevant(string $type): bool
    {
        $classes = $this->getClasses();

        return in_array($type, $classes, true);
    }

    /**
     * @return string[]
     */
    private function getClasses(): array
    {
        return array_keys($this->perClassOldToNewMethods);
    }

    private function isClassRename(array $oldToNewMethods): bool
    {
        $firstMethodConfiguration = current($oldToNewMethods);

        return is_array($firstMethodConfiguration);
    }
}
