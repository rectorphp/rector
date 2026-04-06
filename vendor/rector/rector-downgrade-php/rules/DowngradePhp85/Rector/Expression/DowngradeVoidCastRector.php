<?php

declare (strict_types=1);
namespace Rector\DowngradePhp85\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\Void_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Naming\Naming\VariableNaming;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/marking_return_value_as_important
 * @see \Rector\Tests\DowngradePhp85\Rector\Expression\DowngradeVoidCastRector\DowngradeVoidCastRectorTest
 */
final class DowngradeVoidCastRector extends AbstractRector
{
    /**
     * @readonly
     */
    private VariableNaming $variableNaming;
    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace void casts with proper handling of return values', [new CodeSample(<<<'CODE_SAMPLE'
#[\NoDiscard]
function getPhpVersion(): string
{
    return 'PHP 8.5';
}

(void) getPhpVersion();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[\NoDiscard]
function getPhpVersion(): string
{
    return 'PHP 8.5';
}

$_void = getPhpVersion();
CODE_SAMPLE
)]);
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->expr instanceof Void_) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $variable = new Variable($this->variableNaming->createCountedValueName('_void', $scope));
        // the assign is needed to avoid warning
        // see https://3v4l.org/ie68D#v8.5.3 vs https://3v4l.org/nLc5J#v8.5.3
        $node->expr = new Assign($variable, $node->expr->expr);
        return $node;
    }
}
