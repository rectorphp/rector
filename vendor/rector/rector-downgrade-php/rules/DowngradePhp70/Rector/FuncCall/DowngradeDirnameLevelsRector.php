<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://bugs.php.net/bug.php?id=70112
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeDirnameLevelsRector\DowngradeDirnameLevelsRectorTest
 */
final class DowngradeDirnameLevelsRector extends AbstractRector
{
    /**
     * @var string
     */
    private const DIRNAME = 'dirname';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace the 2nd argument of dirname()', [new CodeSample(<<<'CODE_SAMPLE'
return dirname($path, 2);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return dirname(dirname($path));
CODE_SAMPLE
)]);
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
    public function refactor(Node $node)
    {
        if (!$this->isName($node, 'dirname')) {
            return null;
        }
        $explicitValue = $this->getLevelsRealValue($node);
        if (\is_int($explicitValue)) {
            return $this->refactorForFixedLevels($node, $explicitValue);
        }
        return null;
    }
    private function getLevelsRealValue(FuncCall $funcCall) : ?int
    {
        $args = $funcCall->getArgs();
        $levelsArg = $args[1] ?? null;
        if (!$levelsArg instanceof Arg) {
            return null;
        }
        if ($levelsArg->value instanceof LNumber) {
            return $levelsArg->value->value;
        }
        return null;
    }
    private function refactorForFixedLevels(FuncCall $funcCall, int $levels) : FuncCall
    {
        // keep only the 1st argument
        $funcCall->args = [$funcCall->args[0]];
        for ($i = 1; $i < $levels; ++$i) {
            $funcCall = $this->createDirnameFuncCall(new Arg($funcCall));
        }
        return $funcCall;
    }
    private function createDirnameFuncCall(Arg $pathArg) : FuncCall
    {
        return new FuncCall(new Name(self::DIRNAME), [$pathArg]);
    }
}
