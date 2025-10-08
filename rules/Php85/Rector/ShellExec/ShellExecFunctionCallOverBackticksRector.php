<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\ShellExec;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ShellExec;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_backticks_as_an_alias_for_shell_exec
 * @see \Rector\Tests\Php85\Rector\ShellExec\ShellExecFunctionCallOverBackticksRector\ShellExecFunctionCallOverBackticksRectorTest
 */
final class ShellExecFunctionCallOverBackticksRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace backticks based with shell_exec() function calls', [new CodeSample(<<<'CODE_SAMPLE'
$output = `ls -al`;
echo "<pre>$output</pre>";
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$output = shell_exec('ls -al');
echo "<pre>$output</pre>";
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ShellExec::class];
    }
    /**
     * @param ShellExec $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->parts === []) {
            return null;
        }
        $exprs = [];
        foreach ($node->parts as $part) {
            if ($part instanceof InterpolatedStringPart) {
                $exprs[] = new String_($part->value);
                continue;
            }
            // other parts are Expr (variables, function calls, etc.)
            // keep them as-is so they are concatenated
            $exprs[] = $part;
        }
        // reduce to single concatenated expression
        $argExpr = array_shift($exprs);
        foreach ($exprs as $expr) {
            $argExpr = new Concat($argExpr, $expr);
        }
        // create single Arg and call shell_exec
        return $this->nodeFactory->createFuncCall('shell_exec', [new Arg($argExpr)]);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATE_BACKTICKS;
    }
}
