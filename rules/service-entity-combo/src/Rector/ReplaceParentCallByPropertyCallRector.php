<?php

declare(strict_types=1);

namespace App\Rector;

use App\Rector\ArgumentValueObject\ReplaceParentCallByPropertyArgument;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;

final class ReplaceParentCallByPropertyCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /** @var ReplaceParentCallByPropertyArgument[]  */
    private array $replaceParentCallByPropertyArguments;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Handles method calls in child of Doctrine EntityRepository and moves them to $this->repository property.', []
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->name instanceof Identifier) {
            return null;
        }

        foreach ($this->replaceParentCallByPropertyArguments as $replaceParentCallByPropertyArgument) {
            if (! $this->isObjectType($node->var, $replaceParentCallByPropertyArgument[0])) {
                continue;
            }

            if (! $this->isName($node->name, $replaceParentCallByPropertyArgument[1])) {
                continue;
            }

            $node->var = $this->createPropertyFetch('this', $replaceParentCallByPropertyArgument[2]);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->replaceParentCallByPropertyArguments = $configuration['$replaceParentCallByPropertyArguments'] ?? [];
    }
}