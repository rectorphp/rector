<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;

abstract class AbstractChangeMethodNameRector extends AbstractRector
{
    abstract protected function getClassName(): string;

    abstract protected function getOldMethodName(): string;

    abstract protected function getNewMethodName(): string;

    public function isCandidate(Node $node): bool
    {
        if ($this->isOnTypeCall($node, $this->getClassName())) {
            return true;
        }

        if ($this->isStaticCall($node)) {
            return true;
        }

        return false;
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->name->name = $this->getNewMethodName();

        return $node;
    }

    private function isStaticCall(Node $node): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
            return false;
        }

        if ($node->class->toString() !== $this->getClassName()) {
            return false;
        }

        return (string) $node->name === $this->getOldMethodName();
    }

    private function isOnTypeCall(Node $node, string $class): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof Variable) {
            return false;
        }

        return $node->var->getAttribute('type') === $class;
    }
}
