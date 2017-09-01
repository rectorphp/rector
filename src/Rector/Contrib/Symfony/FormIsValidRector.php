<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractRector;

/**
 * Converts all:
 * $form->isValid()
 *
 * into:
 * $form->isSubmitted() && $form->isValid()
 */
final class FormIsValidRector extends AbstractRector
{
    public function getSetName(): string
    {
        return SetNames::SYMFONY;
    }

    public function sinceVersion(): float
    {
        return 4.0;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if ($node->var->getAttribute('type') !== 'Symfony\Component\Form\Form') {
            return false;
        }

        if ((string) $node->name !== 'isValid') {
            return false;
        }

        if ($node->getAttribute('prev') !== null) {
            return false;
        }

        return true;
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $varName = $node->var->name;

        return new BooleanAnd(
            $this->createMethodCall($varName, 'isSubmitted'),
            $this->createMethodCall($varName, 'isValid')
        );
    }

    private function createMethodCall(string $varName, string $methodName): MethodCall
    {
        $varNode = new Variable($varName);

        return new MethodCall($varNode, $methodName);
    }
}
