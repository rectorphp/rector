<?php declare(strict_types=1);

namespace Rector\Nette\Rector\DI;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Covers https://github.com/Kdyby/Doctrine/pull/269/files
 */
final class SetEntityToStatementRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var string
     */
    private $compilerExtensionClass;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        NodeFactory $nodeFactory,
        string $compilerExtensionClass = 'Nette\DI\CompilerExtension'
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->nodeFactory = $nodeFactory;
        $this->compilerExtensionClass = $compilerExtensionClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns setDefinition() to Nette\DI\Helpers::expand() value in Nette\DI\CompilerExtension',
            [
                new CodeSample(
                    '$definition->setEntity("someEntity");',
                    '$definition = new Statement("someEntity", $definition->arguments);'
                ),
            ]
        );
    }

    public function isCandidate(Node $node): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== $this->compilerExtensionClass) {
            return false;
        }

        return $this->methodCallAnalyzer->isMethod($node, 'setEntity');
    }

    /**
     * @param MethodCall $methodCallNode
     *
     * Returns $variable = new Nette\DI\Statement($oldArg, $variable->arguments);
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        return new Assign(
            $methodCallNode->var,
            new New_(
                new Name('Nette\DI\Statement'),
                [
                    $methodCallNode->args[0],
                    $this->nodeFactory->createArg(new PropertyFetch($methodCallNode->var, 'arguments')),
                ]
            )
        );
    }
}
