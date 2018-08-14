<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Process;

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

    /**
     * @var string
     */
    private $processBuilderClass;

    public function __construct(
        StaticMethodCallAnalyzer $staticMethodCallAnalyzer,
        string $processBuilderClass = 'Symfony\Component\Process\ProcessBuilder'
    ) {
        $this->staticMethodCallAnalyzer = $staticMethodCallAnalyzer;
        $this->processBuilderClass = $processBuilderClass;
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

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $staticCallNode
     */
    public function refactor(Node $staticCallNode): ?Node
    {
        if ($this->staticMethodCallAnalyzer->isTypeAndMethod(
            $staticCallNode,
            $this->processBuilderClass,
            'create'
        ) === false) {
            return null;
        }
        return new New_($staticCallNode->class, $staticCallNode->args);
    }
}
