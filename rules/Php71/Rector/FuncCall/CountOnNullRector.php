<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/Bndc9
 */
final class CountOnNullRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::COUNT_ON_NULL;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes count() on null to safe ternary check', [new CodeSample(<<<'CODE_SAMPLE'
$values = null;
$count = count($values);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$values = null;
$count = $values === null ? 0 : count($values);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class, Ternary::class];
    }
    /**
     * @param FuncCall|Ternary $node
     * @return int|\PhpParser\Node\Expr\Ternary|null|\PhpParser\Node\Expr\FuncCall
     */
    public function refactorWithScope(Node $node, Scope $scope)
    {
        \trigger_error(\sprintf('The "%s" rule is now deprecated as often reports false positives and requires custom refactoring of previous code. Use PHPStan to spot nullable arguments and refactor code to fit your use case.', self::class), \E_USER_ERROR);
    }
}
