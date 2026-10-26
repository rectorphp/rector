<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\Int_;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see https://3v4l.org/cVPim
 * @see \Rector\Tests\Php81\Rector\FuncCall\NullToStrictIntPregSlitFuncCallLimitArgRector\NullToStrictIntPregSlitFuncCallLimitArgRectorTest
 */
final class NullToStrictIntPregSlitFuncCallLimitArgRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ArgsAnalyzer $argsAnalyzer;
    public function __construct(ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change null to strict int defined preg_split limit arg function call argument', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_split('/\s/', $output, NULL, PREG_SPLIT_NO_EMPTY)
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_split('/\s/', $output, 0, PREG_SPLIT_NO_EMPTY)
    }
}
CODE_SAMPLE
)]);
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
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $args = $node->getArgs();
        $position = $this->argsAnalyzer->resolveArgPosition($args, 'limit', 2);
        if (!isset($args[$position])) {
            return null;
        }
        $activeArg = $args[$position];
        if (!$this->getType($activeArg->value)->isNull()->yes()) {
            return null;
        }
        $activeArg->value = new Int_(0);
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_81;
    }
    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (!$this->isName($funcCall, 'preg_split')) {
            return \true;
        }
        return $funcCall->isFirstClassCallable();
    }
}
