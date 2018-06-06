<?php declare(strict_types=1);

namespace Rector\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\IdentifierRenamer;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class MethodCallToAnotherMethodCallWithArgumentsRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var string
     */
    private $oldMethod;

    /**
     * @var string
     */
    private $newMethod;

    /**
     * @var string
     */
    private $serviceDefinitionClass;

    /**
     * @var string[]
     */
    private $newMethodArguments = [];

    /**
     * @param string[] $newMethodArguments
     */
    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        IdentifierRenamer $identifierRenamer,
        NodeFactory $nodeFactory,
        string $serviceDefinitionClass = 'Nette\DI\ServiceDefinition',
        string $oldMethod = 'setInject',
        string $newMethod = 'addTag',
        array $newMethodArguments = ['inject']
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
        $this->nodeFactory = $nodeFactory;
        $this->serviceDefinitionClass = $serviceDefinitionClass;
        $this->oldMethod = $oldMethod;
        $this->newMethod = $newMethod;
        $this->newMethodArguments = $newMethodArguments;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns old method call with specfici type to new one with arguments', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$serviceDefinition = new Nette\DI\ServiceDefinition;
$serviceDefinition->setInject();
$END
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$serviceDefinition = new Nette\DI\ServiceDefinition;
$serviceDefinition->addTag('inject');
CODE_SAMPLE
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypeAndMethods($node, $this->serviceDefinitionClass, [$this->oldMethod])) {
            return false;
        }

        return true;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->identifierRenamer->renameNode($methodCallNode, $this->newMethod);
        $methodCallNode->args = $this->nodeFactory->createArgs($this->newMethodArguments);

        return $methodCallNode;
    }
}
