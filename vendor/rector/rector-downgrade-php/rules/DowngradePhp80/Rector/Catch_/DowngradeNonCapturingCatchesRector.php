<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Catch_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Catch_;
use Rector\Naming\Naming\VariableNaming;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/non-capturing_catches
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector\DowngradeNonCapturingCatchesRectorTest
 */
final class DowngradeNonCapturingCatchesRector extends AbstractRector
{
    /**
     * @readonly
     */
    private VariableNaming $variableNaming;
    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade catch () without variable to one', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            // code
        } catch (\Exception) {
            // error
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            // code
        } catch (\Exception $exception) {
            // error
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Catch_::class];
    }
    /**
     * @param Catch_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->var instanceof Variable) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $exceptionVarName = $this->variableNaming->createCountedValueName('exception', $scope);
        $node->var = new Variable($exceptionVarName);
        return $node;
    }
}
