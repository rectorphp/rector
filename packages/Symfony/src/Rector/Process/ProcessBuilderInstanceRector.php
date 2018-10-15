<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Process;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ProcessBuilderInstanceRector extends AbstractRector
{
    /**
     * @var string
     */
    private $processBuilderClass;

    public function __construct(string $processBuilderClass = 'Symfony\Component\Process\ProcessBuilder')
    {
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

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, $this->processBuilderClass)) {
            return null;
        }

        if (! $this->isName($node, 'create')) {
            return null;
        }

        return new New_($node->class, $node->args);
    }
}
