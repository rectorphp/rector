<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\DI;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\IdentifierRenamer;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SetInjectToAddTagRector extends AbstractRector
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
    private $relatedClass = 'Nette\DI\ServiceDefinition';

    /**
     * @var string
     */
    private $oldMethod = 'setInject';

    /**
     * @var string
     */
    private $newMethod = 'addTag';

    /**
     * @var string[]
     */
    private $newArguments = ['inject'];

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        IdentifierRenamer $identifierRenamer,
        NodeFactory $nodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
        $this->nodeFactory = $nodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns setInject() to tag in Nette\DI\CompilerExtension', [
            new CodeSample('$serviceDefinition->setInject();', '$serviceDefinition->addTag("inject");'),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypeAndMethods($node, $this->relatedClass, [$this->oldMethod])) {
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
        $methodCallNode->args = $this->nodeFactory->createArgs($this->newArguments);

        return $methodCallNode;
    }
}
