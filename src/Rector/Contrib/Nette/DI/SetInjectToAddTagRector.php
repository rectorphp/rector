<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\DI;

use PhpParser\Node;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;

final class SetInjectToAddTagRector extends AbstractRector
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

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, NodeFactory $nodeFactory)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isMethodCallTypeAndMethods(
            $node,
            $this->relatedClass,
            [$this->oldMethod]
        )) {
            return false;
        }

        return true;
    }

    /**
     * @param Node\Expr\MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->name->name = $this->newMethod;
        $node->args = $this->nodeFactory->createArgs($this->newArguments);

        return $node;
    }
}
