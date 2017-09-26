<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\Exception\NotImplementedException;
use Rector\Node\Attribute;
use Rector\NodeValueResolver\NodeValueResolver;

abstract class AbstractChangeMethodNameRector extends AbstractRector
{
    /**
     * @var string|null
     */
    private $activeType;

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
        $oldToNewMethods = $this->getPerClassOldToNewMethods()[$this->activeType];

        foreach ($oldToNewMethods as $oldMethod => $newMethod) {
            $methodName = $node->name->name;
            if ($methodName !== $oldMethod) {
                continue;
            }

            $node->name->name = $newMethod;
        }

        return $node;
    }

    /**
     * @return string[][] { class => [ oldMethod => newMethod ] }
     */
    abstract protected function getPerClassOldToNewMethods(): array;

    private function isOnTypeCall(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof Variable) {
            return false;
        }

        /** @var string $type */
        $type = $node->var->getAttribute(Attribute::TYPE);

        $this->ensureVariableHasType($node->var, $type);

        if (! $this->isTypeRelevant($type)) {
            return false;
        }

        $this->activeType = $type;

        return true;
    }

    private function isStaticCallOnType(Node $node): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
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
        return array_keys($this->getPerClassOldToNewMethods());
    }

    private function ensureVariableHasType(Variable $variableNode, $type): void
    {
        if ($type === null) {
            throw new NotImplementedException(sprintf(
                '%s() was unable to resolve. Type for "%s" with "%s" name was null. Try to fix %s.',
                __METHOD__,
                get_class($variableNode),
                '$' . (string) $variableNode->name,
                NodeValueResolver::class
            ));
        }
    }
}
