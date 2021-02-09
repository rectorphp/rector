<?php

declare(strict_types=1);

namespace Rector\Removing\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @sponsor Thanks https://twitter.com/afilina & Zenika (CAN) for sponsoring this rule - visit them on https://zenika.ca/en/en
 *
 * @see \Rector\Removing\Tests\Rector\FuncCall\RemoveFuncCallArgRector\RemoveFuncCallArgRectorTest
 */
final class RemoveFuncCallArgRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const REMOVED_FUNCTION_ARGUMENTS = 'removed_function_arguments';

    /**
     * @var RemoveFuncCallArg[]
     */
    private $removedFunctionArguments = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove argument by position by function name', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
remove_last_arg(1, 2);
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
remove_last_arg(1);
CODE_SAMPLE
                , [
                    self::REMOVED_FUNCTION_ARGUMENTS => [new RemoveFuncCallArg('remove_last_arg', 1)],
                ]),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->removedFunctionArguments as $removedFunctionArgument) {
            if (! $this->isName($node->name, $removedFunctionArgument->getFunction())) {
                continue;
            }

            foreach (array_keys($node->args) as $position) {
                if ($removedFunctionArgument->getArgumentPosition() !== $position) {
                    continue;
                }

                $this->nodeRemover->removeArg($node, $position);
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $removedFunctionArguments = $configuration[self::REMOVED_FUNCTION_ARGUMENTS] ?? [];
        Assert::allIsInstanceOf($removedFunctionArguments, RemoveFuncCallArg::class);
        $this->removedFunctionArguments = $removedFunctionArguments;
    }
}
