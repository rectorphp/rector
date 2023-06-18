<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\Break_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\NodeTraverser;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeNestingScope\ContextAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/questions/3618030/php-fatal-error-cannot-break-continue https://stackoverflow.com/questions/11988281/why-does-cannot-break-continue-1-level-comes-in-php
 *
 * @changelog https://3v4l.org/Qtelt
 * @see \Rector\Tests\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector\BreakNotInLoopOrSwitchToReturnRectorTest
 */
final class BreakNotInLoopOrSwitchToReturnRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ContextAnalyzer
     */
    private $contextAnalyzer;
    /**
     * @var string
     */
    private const IS_BREAK_IN_SWITCH = 'is_break_in_switch';
    public function __construct(ContextAnalyzer $contextAnalyzer)
    {
        $this->contextAnalyzer = $contextAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NO_BREAK_OUTSIDE_LOOP;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert break outside for/foreach/switch context to return', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($isphp5)
            return 1;
        else
            return 2;
        break;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($isphp5)
            return 1;
        else
            return 2;
        return;
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
        return [Switch_::class, Break_::class];
    }
    /**
     * @param Switch_|Break_ $node
     * @return \PhpParser\Node\Stmt\Return_|null|int
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Switch_) {
            $this->traverseNodesWithCallable($node->cases, static function (Node $subNode) : ?int {
                if ($subNode instanceof Class_ || $subNode instanceof FunctionLike && !$subNode instanceof ArrowFunction) {
                    return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                }
                if (!$subNode instanceof Break_) {
                    return null;
                }
                $subNode->setAttribute(self::IS_BREAK_IN_SWITCH, \true);
                return null;
            });
            return null;
        }
        if ($this->contextAnalyzer->isInLoop($node)) {
            return null;
        }
        if ($node->getAttribute(self::IS_BREAK_IN_SWITCH) === \true) {
            return null;
        }
        if ($this->contextAnalyzer->isInIf($node)) {
            return new Return_();
        }
        return NodeTraverser::REMOVE_NODE;
    }
}
