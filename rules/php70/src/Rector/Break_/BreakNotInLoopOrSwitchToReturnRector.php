<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\Break_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeNestingScope\ContextAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://3v4l.org/Qtelt
 * @see https://stackoverflow.com/questions/3618030/php-fatal-error-cannot-break-continue
 * @see https://stackoverflow.com/questions/11988281/why-does-cannot-break-continue-1-level-comes-in-php
 *
 * @see \Rector\Php70\Tests\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector\BreakNotInLoopOrSwitchToReturnRectorTest
 */
final class BreakNotInLoopOrSwitchToReturnRector extends AbstractRector
{
    /**
     * @var ContextAnalyzer
     */
    private $contextAnalyzer;

    public function __construct(ContextAnalyzer $contextAnalyzer)
    {
        $this->contextAnalyzer = $contextAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Convert break outside for/foreach/switch context to return',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Break_::class];
    }

    /**
     * @param Break_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->contextAnalyzer->isInLoop($node)) {
            return null;
        }

        if ($this->contextAnalyzer->isInIf($node)) {
            return new Return_();
        }

        $this->removeNode($node);

        return $node;
    }
}
