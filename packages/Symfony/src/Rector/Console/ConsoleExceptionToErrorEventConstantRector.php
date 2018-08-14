<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Console;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\ClassConstAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Covers:
 * - https://github.com/symfony/symfony/pull/22441/files
 * - https://github.com/symfony/symfony/blob/master/UPGRADE-3.3.md#console
 */
final class ConsoleExceptionToErrorEventConstantRector extends AbstractRector
{
    /**
     * @var string
     */
    private const CONSOLE_EVENTS_CLASS = 'Symfony\Component\Console\ConsoleEvents';

    /**
     * @var ClassConstAnalyzer
     */
    private $classConstAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(ClassConstAnalyzer $classConstAnalyzer, NodeFactory $nodeFactory)
    {
        $this->classConstAnalyzer = $classConstAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns old event name with EXCEPTION to ERROR constant in Console in Symfony', [
            new CodeSample('"console.exception"', 'Symfony\Component\Console\ConsoleEvents::ERROR'),
            new CodeSample(
                'Symfony\Component\Console\ConsoleEvents::EXCEPTION',
                'Symfony\Component\Console\ConsoleEvents::ERROR'
            ),
        ]);
    }

    public function getNodeType(): string
    {
        return [ClassConstFetch::class, String_::class];
    }

    /**
     * @param ClassConstFetch|String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->classConstAnalyzer->isTypeAndNames($node, self::CONSOLE_EVENTS_CLASS, ['EXCEPTION'])) {
        }
        if (! $node instanceof String_) {
            return null;
        }
        if (($node->value === 'console.exception') === false) {
            return null;
        }
        return $this->nodeFactory->createClassConstant(self::CONSOLE_EVENTS_CLASS, 'ERROR');
    }
}
