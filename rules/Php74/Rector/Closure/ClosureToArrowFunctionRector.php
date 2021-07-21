<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php74\NodeAnalyzer\ClosureArrowFunctionAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/arrow_functions_v2
 *
 * @see \Rector\Tests\Php74\Rector\Closure\ClosureToArrowFunctionRector\ClosureToArrowFunctionRectorTest
 */
final class ClosureToArrowFunctionRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var \Rector\Php74\NodeAnalyzer\ClosureArrowFunctionAnalyzer
     */
    private $closureArrowFunctionAnalyzer;
    public function __construct(\Rector\Php74\NodeAnalyzer\ClosureArrowFunctionAnalyzer $closureArrowFunctionAnalyzer)
    {
        $this->closureArrowFunctionAnalyzer = $closureArrowFunctionAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change closure to arrow function', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($meetups)
    {
        return array_filter($meetups, function (Meetup $meetup) {
            return is_object($meetup);
        });
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($meetups)
    {
        return array_filter($meetups, fn(Meetup $meetup) => is_object($meetup));
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
        return [\PhpParser\Node\Expr\Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $returnExpr = $this->closureArrowFunctionAnalyzer->matchArrowFunctionExpr($node);
        if (!$returnExpr instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $arrowFunction = new \PhpParser\Node\Expr\ArrowFunction();
        $arrowFunction->params = $node->params;
        $arrowFunction->returnType = $node->returnType;
        $arrowFunction->byRef = $node->byRef;
        $arrowFunction->expr = $returnExpr;
        if ($node->static) {
            $arrowFunction->static = \true;
        }
        return $arrowFunction;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::ARROW_FUNCTION;
    }
}
