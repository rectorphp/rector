<?php

declare (strict_types=1);
namespace Rector\Removing\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeRemoval\BreakingRemovalGuard;
use Rector\Removing\ValueObject\RemoveFuncCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Removing\Rector\FuncCall\RemoveFuncCallRector\RemoveFuncCallRectorTest
 */
final class RemoveFuncCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var RemoveFuncCall[]
     */
    private $removeFuncCalls = [];
    /**
     * @readonly
     * @var \Rector\NodeRemoval\BreakingRemovalGuard
     */
    private $breakingRemovalGuard;
    public function __construct(BreakingRemovalGuard $breakingRemovalGuard)
    {
        $this->breakingRemovalGuard = $breakingRemovalGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove ini_get by configuration', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
ini_get('y2k_compliance');
ini_get('keep_me');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
ini_get('keep_me');
CODE_SAMPLE
, [new RemoveFuncCall('ini_get', [1 => ['y2k_compliance']])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($this->removeFuncCalls as $removeFuncCall) {
            if (!$this->isName($node, $removeFuncCall->getFuncCall())) {
                continue;
            }
            if ($removeFuncCall->getArgumentPositionAndValues() === []) {
                $this->removeNode($node);
                return $node;
            }
            $removedFuncCall = $this->refactorFuncCallsWithPositions($node, $removeFuncCall);
            if ($removedFuncCall instanceof FuncCall) {
                return $node;
            }
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, RemoveFuncCall::class);
        $this->removeFuncCalls = $configuration;
    }
    private function refactorFuncCallsWithPositions(FuncCall $funcCall, RemoveFuncCall $removeFuncCall) : ?FuncCall
    {
        foreach ($removeFuncCall->getArgumentPositionAndValues() as $argumentPosition => $values) {
            if (!$this->isArgumentPositionValueMatch($funcCall, $argumentPosition, $values)) {
                continue;
            }
            if ($this->breakingRemovalGuard->isLegalNodeRemoval($funcCall)) {
                $this->removeNode($funcCall);
                return $funcCall;
            }
        }
        return null;
    }
    /**
     * @param mixed[] $values
     */
    private function isArgumentPositionValueMatch(FuncCall $funcCall, int $argumentPosition, array $values) : bool
    {
        if (!isset($funcCall->args[$argumentPosition])) {
            return \false;
        }
        if (!$funcCall->args[$argumentPosition] instanceof Arg) {
            return \false;
        }
        return $this->valueResolver->isValues($funcCall->args[$argumentPosition]->value, $values);
    }
}
