<?php

declare(strict_types=1);

namespace Rector\Removing\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeRemoval\BreakingRemovalGuard;
use Rector\Removing\ValueObject\RemoveFuncCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @sponsor Thanks https://twitter.com/afilina & Zenika (CAN) for sponsoring this rule - visit them on https://zenika.ca/en/en
 *
 * @see \Rector\Removing\Tests\Rector\FuncCall\RemoveFuncCallRector\RemoveFuncCallRectorTest
 */
final class RemoveFuncCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const REMOVE_FUNC_CALLS = 'remove_func_calls';

    /**
     * @var RemoveFuncCall[]
     */
    private $removeFuncCalls = [];

    /**
     * @var BreakingRemovalGuard
     */
    private $breakingRemovalGuard;

    public function __construct(BreakingRemovalGuard $breakingRemovalGuard)
    {
        $this->breakingRemovalGuard = $breakingRemovalGuard;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        $configuration = [
            self::REMOVE_FUNC_CALLS => [
                new RemoveFuncCall('ini_get', [
                    1 => ['y2k_compliance'],
                ]),
            ],
        ];
        return new RuleDefinition('Remove ini_get by configuration', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
ini_get('y2k_compliance');
ini_get('keep_me');
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
ini_get('keep_me');
CODE_SAMPLE
                ,
                $configuration
            ), ]
        );
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
        foreach ($this->removeFuncCalls as $removeFuncCall) {
            if (! $this->isName($node, $removeFuncCall->getFuncCall())) {
                continue;
            }

            if ($removeFuncCall->getArgumentPositionAndValues() === []) {
                $this->removeNode($node);
                return null;
            }

            $this->refactorFuncCallsWithPositions($node, $removeFuncCall);
        }

        return null;
    }

    /**
     * @param array<string, RemoveFuncCall[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $removeFuncCalls = $configuration[self::REMOVE_FUNC_CALLS] ?? [];
        Assert::allIsInstanceOf($removeFuncCalls, RemoveFuncCall::class);
        $this->removeFuncCalls = $removeFuncCalls;
    }

    private function refactorFuncCallsWithPositions(FuncCall $funcCall, RemoveFuncCall $removeFuncCall): void
    {
        foreach ($removeFuncCall->getArgumentPositionAndValues() as $argumentPosition => $values) {
            if (! $this->isArgumentPositionValueMatch($funcCall, $argumentPosition, $values)) {
                continue;
            }

            if ($this->breakingRemovalGuard->isLegalNodeRemoval($funcCall)) {
                $this->removeNode($funcCall);
            }
        }
    }

    /**
     * @param mixed[] $values
     */
    private function isArgumentPositionValueMatch(FuncCall $funcCall, int $argumentPosition, array $values): bool
    {
        if (! isset($funcCall->args[$argumentPosition])) {
            return false;
        }

        return $this->valueResolver->isValues($funcCall->args[$argumentPosition]->value, $values);
    }
}
