<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Process;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ProcessBuilderGetProcessRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var string
     */
    private $processBuilderClass;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        string $processBuilderClass = 'Symfony\Component\Process\ProcessBuilder'
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->processBuilderClass = $processBuilderClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes `$processBuilder->getProcess()` calls to $processBuilder in Process in Symfony, because ProcessBuilder was removed. This is part of multi-step Rector and has very narrow focus.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$processBuilder = new Symfony\Component\Process\ProcessBuilder;
$process = $processBuilder->getProcess();
$commamdLine = $processBuilder->getProcess()->getCommandLine();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$processBuilder = new Symfony\Component\Process\ProcessBuilder;
$process = $processBuilder;
$commamdLine = $processBuilder->getCommandLine();
CODE_SAMPLE
                ),
            ]
        );
    }

    public function isCandidate(Node $node): bool
    {
        return $this->methodCallAnalyzer->isTypeAndMethod($node, $this->processBuilderClass, 'getProcess');
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        return $methodCallNode->var;
    }
}
