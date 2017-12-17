<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Process;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Part of multi-step Rector
 *
 * Before:
 * - $processBuilder = new Symfony\Component\Process\ProcessBuilder;
 * - $process = $processBuilder->getProcess();
 *
 * After:
 * - $processBuilder = new Symfony\Component\Process\ProcessBuilder;
 * - $process = $processBuilder;
 */
final class ProcessBuilderGetProcessRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        return $this->methodCallAnalyzer->isTypeAndMethod($node,'Symfony\Component\Process\ProcessBuilder', 'getProcess');
    }

    /**
     * @param MethodCall $methodCallNode
     * @return null|Node
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        return $methodCallNode->var;
    }
}
