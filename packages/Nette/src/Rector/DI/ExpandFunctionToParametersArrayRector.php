<?php declare(strict_types=1);

namespace Rector\Nette\Rector\DI;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Covers https://github.com/Kdyby/Doctrine/pull/298/files
 */
final class ExpandFunctionToParametersArrayRector extends AbstractRector
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
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var string
     */
    private $compilerExtensionClass;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        MethodArgumentAnalyzer $methodArgumentAnalyzer,
        NodeFactory $nodeFactory,
        string $compilerExtensionClass = 'Nette\DI\CompilerExtension'
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodArgumentAnalyzer = $methodArgumentAnalyzer;
        $this->nodeFactory = $nodeFactory;
        $this->compilerExtensionClass = $compilerExtensionClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns expand() to parameters value in Nette\DI\CompilerExtension', [
            new CodeSample('$builder->expand("argument");', '$builder->parameters["argument"];'),
            new CodeSample('$builder->expand("%argument%");', '$builder->parameters["argument"];'),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== $this->compilerExtensionClass) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethod($node, 'expand')) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        return $this->methodArgumentAnalyzer->isMethodNthArgumentString($methodCallNode, 1);
    }

    /**
     * @param MethodCall $methodCallNode
     *
     * Returns $builder->parameters['argument']
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        /** @var String_ $argument */
        $argument = $methodCallNode->args[0]->value;
        $argument->value = Strings::trim($argument->value, '%');

        return $this->nodeFactory->createVariablePropertyArrayFetch($methodCallNode->var, 'parameters', $argument);
    }
}
