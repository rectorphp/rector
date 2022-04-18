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
use RectorPrefix20220418\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Removing\Rector\FuncCall\RemoveFuncCallRector\RemoveFuncCallRectorTest
 */
final class RemoveFuncCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
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
    public function __construct(\Rector\NodeRemoval\BreakingRemovalGuard $breakingRemovalGuard)
    {
        $this->breakingRemovalGuard = $breakingRemovalGuard;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove ini_get by configuration', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
ini_get('y2k_compliance');
ini_get('keep_me');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
ini_get('keep_me');
CODE_SAMPLE
, [new \Rector\Removing\ValueObject\RemoveFuncCall('ini_get', [1 => ['y2k_compliance']])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
            if ($removedFuncCall instanceof \PhpParser\Node\Expr\FuncCall) {
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
        \RectorPrefix20220418\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Removing\ValueObject\RemoveFuncCall::class);
        $this->removeFuncCalls = $configuration;
    }
    private function refactorFuncCallsWithPositions(\PhpParser\Node\Expr\FuncCall $funcCall, \Rector\Removing\ValueObject\RemoveFuncCall $removeFuncCall) : ?\PhpParser\Node\Expr\FuncCall
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
    private function isArgumentPositionValueMatch(\PhpParser\Node\Expr\FuncCall $funcCall, int $argumentPosition, array $values) : bool
    {
        if (!isset($funcCall->args[$argumentPosition])) {
            return \false;
        }
        if (!$funcCall->args[$argumentPosition] instanceof \PhpParser\Node\Arg) {
            return \false;
        }
        return $this->valueResolver->isValues($funcCall->args[$argumentPosition]->value, $values);
    }
}
