<?php declare(strict_types=1);

namespace Rector\Nette\Rector\DI;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Covers https://github.com/Kdyby/Doctrine/pull/298/files
 */
final class ExpandFunctionToStaticExpandFunctionRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodArgumentAnalyzer
     */
    private $methodArgumentAnalyzer;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var string
     */
    private $compilerExtensionClass;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        MethodArgumentAnalyzer $methodArgumentAnalyzer,
        BuilderFactory $builderFactory,
        string $compilerExtensionClass = 'Nette\DI\CompilerExtension'
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodArgumentAnalyzer = $methodArgumentAnalyzer;
        $this->builderFactory = $builderFactory;
        $this->compilerExtensionClass = $compilerExtensionClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns expand() to Nette\DI\Helpers::expand() value in Nette\DI\CompilerExtension',
            [
                new CodeSample(
                    '$builder->expand(object|array)',
                    '\Nette\DI\Helpers::expand(object|array, $builder->parameters);'
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

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        if (! $this->methodCallAnalyzer->isMethod($methodCallNode, 'expand')
            || $this->methodArgumentAnalyzer->isMethodNthArgumentString($methodCallNode, 1)) {
            return false;
        }

        return true;
    }

    /**
     * @param MethodCall $methodCallNode
     *
     * Returns \Nette\DI\Helpers::expand('argument', $builder->parameters)
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $arguments = [$methodCallNode->args[0], new PropertyFetch($methodCallNode->var, 'parameters')];

        return $this->builderFactory->staticCall('Nette\DI\Helpers', 'expand', $arguments);
    }
}
