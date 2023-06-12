<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Name;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/match_expression_v2
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\DowngradeMatchToSwitchRectorTest
 */
final class DowngradeMatchToSwitchRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade match() to switch()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $message = match ($statusCode) {
            200, 300 => null,
            400 => 'not found',
            default => 'unknown status code',
        };
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $message = ($statusCode === 200 || $statusCode === 300 ? null : $statusCode === 400 ? 'not found' : 'unknown status code';
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
        return [Match_::class];
    }
    /**
     * @param Match_ $node
     */
    public function refactor(Node $node) : ?Ternary
    {
        $reversedMatchArms = \array_reverse($node->arms);
        $defaultExpr = $this->matchDefaultExpr($node);
        $defaultExpr = $defaultExpr ?: new ConstFetch(new Name('null'));
        // @see https://wiki.php.net/rfc/throw_expression
        // throws expr is not allowed → replace temporarily
        if ($defaultExpr instanceof Throw_ && $this->phpVersionProvider->provide() < PhpVersion::PHP_80) {
            $defaultExpr = new ConstFetch(new Name('null'));
        }
        $currentTernary = null;
        foreach ($reversedMatchArms as $reversedMatchArm) {
            if ($reversedMatchArm->conds === null) {
                continue;
            }
            $cond = $this->createCond($reversedMatchArm->conds, $node);
            $currentTernary = new Ternary($cond, $reversedMatchArm->body, $currentTernary ?: $defaultExpr);
        }
        return $currentTernary;
    }
    private function matchDefaultExpr(Match_ $match) : ?Expr
    {
        foreach ($match->arms as $matchArm) {
            if ($matchArm->conds === null) {
                return $matchArm->body;
            }
        }
        return null;
    }
    /**
     * @param Expr[] $condExprs
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\BooleanOr
     */
    private function createCond(array $condExprs, Match_ $match)
    {
        $cond = null;
        foreach ($condExprs as $condExpr) {
            if ($cond instanceof Node) {
                $cond = new BooleanOr($cond, new Identical($match->cond, $condExpr));
            } else {
                $cond = new Identical($match->cond, $condExpr);
            }
        }
        if (!$cond instanceof Expr) {
            throw new ShouldNotHappenException();
        }
        return $cond;
    }
}
