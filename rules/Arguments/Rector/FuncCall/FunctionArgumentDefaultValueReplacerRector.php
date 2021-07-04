<?php

declare(strict_types=1);

namespace Rector\Arguments\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Arguments\ArgumentDefaultValueReplacer;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @changelog https://php.watch/versions/8.1/version_compare-operator-restrictions
 * @changelog https://github.com/rectorphp/rector/issues/6271
 *
 * @see \Rector\Tests\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector\FunctionArgumentDefaultValueReplacerRectorTest
 */
final class FunctionArgumentDefaultValueReplacerRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const REPLACED_ARGUMENTS = 'replaced_arguments';

    /**
     * @var ReplaceFuncCallArgumentDefaultValue[]
     */
    private mixed $replacedArguments = [];

    public function __construct(
        private ArgumentDefaultValueReplacer $argumentDefaultValueReplacer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Streamline the operator arguments of version_compare function', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
version_compare(PHP_VERSION, '5.6', 'gte');
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
version_compare(PHP_VERSION, '5.6', 'ge');
CODE_SAMPLE
            ,
                [
                    self::REPLACED_ARGUMENTS => [
                        new ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'gte', 'ge',),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): FuncCall
    {
        foreach ($this->replacedArguments as $replacedArgument) {
            if (! $this->isName($node->name, $replacedArgument->getFunction())) {
                continue;
            }

            $this->argumentDefaultValueReplacer->processReplaces($node, $replacedArgument);
        }

        return $node;
    }

    /**
     * @param array<string, ReplaceFuncCallArgumentDefaultValue[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $replacedArguments = $configuration[self::REPLACED_ARGUMENTS] ?? [];
        Assert::allIsInstanceOf($replacedArguments, ReplaceFuncCallArgumentDefaultValue::class);
        $this->replacedArguments = $replacedArguments;
    }
}
