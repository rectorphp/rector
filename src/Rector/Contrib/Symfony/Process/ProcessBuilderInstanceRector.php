<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Process;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeAnalyzer\StaticMethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ProcessBuilderInstanceRector extends AbstractRector
{
    /**
     * @var StaticMethodCallAnalyzer
     */
    private $staticMethodCallAnalyzer;

    public function __construct(StaticMethodCallAnalyzer $staticMethodCallAnalyzer)
    {
        $this->staticMethodCallAnalyzer = $staticMethodCallAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.',
            [
                new CodeSample(
                    '$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);',
                    '$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);'
                ),
            ]
        );
    }

    public function isCandidate(Node $node): bool
    {
        return $this->staticMethodCallAnalyzer->isTypeAndMethod(
            $node,
            'Symfony\Component\Process\ProcessBuilder',
            'create'
        );
    }

    /**
     * @param StaticCall $staticCallNode
     */
    public function refactor(Node $staticCallNode): ?Node
    {
        return new New_($staticCallNode->class, $staticCallNode->args);
    }
}
