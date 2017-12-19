<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Process;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeAnalyzer\StaticMethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Part of multi-step Rector
 *
 * Before:
 * - $processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
 *
 * After:
 * - $processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
 */
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
