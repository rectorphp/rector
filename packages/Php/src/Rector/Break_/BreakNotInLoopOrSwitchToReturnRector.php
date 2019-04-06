<?php declare(strict_types=1);

namespace Rector\Php\Rector\Break_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Break_;
use Rector\Context\ContextAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/Qtelt
 * @see https://stackoverflow.com/questions/3618030/php-fatal-error-cannot-break-continue
 * @see https://stackoverflow.com/questions/11988281/why-does-cannot-break-continue-1-level-comes-in-php
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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert break outside for/foreach/switch context to return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $zhrs = abs($gmt)/3600;
        $hrs = floor($zhrs);
        if ($isphp5)
            return sprintf('%s%02d%02d',($gmt<=0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
        else
            return sprintf('%s%02d%02d',($gmt<0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
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
        $zhrs = abs($gmt)/3600;
        $hrs = floor($zhrs);
        if ($isphp5)
            return sprintf('%s%02d%02d',($gmt<=0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
        else
            return sprintf('%s%02d%02d',($gmt<0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
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
            return new Node\Stmt\Return_();
        }

        $this->removeNode($node);

        return $node;
    }
}
