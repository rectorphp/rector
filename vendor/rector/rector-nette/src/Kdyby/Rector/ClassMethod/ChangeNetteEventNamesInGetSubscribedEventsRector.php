<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\Rector\ClassMethod;

use RectorPrefix20220501\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\Kdyby\NodeAnalyzer\GetSubscribedEventsClassMethodAnalyzer;
use Rector\Nette\Kdyby\NodeManipulator\GetSubscribedEventsArrayManipulator;
use Rector\Nette\Kdyby\NodeManipulator\ListeningClassMethodArgumentManipulator;
use Rector\Nette\Kdyby\NodeResolver\ListeningMethodsCollector;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Kdyby\Rector\ClassMethod\ChangeNetteEventNamesInGetSubscribedEventsRector\ChangeNetteEventNamesInGetSubscribedEventsRectorTest
 */
final class ChangeNetteEventNamesInGetSubscribedEventsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Nette\Kdyby\NodeManipulator\GetSubscribedEventsArrayManipulator
     */
    private $getSubscribedEventsArrayManipulator;
    /**
     * @readonly
     * @var \Rector\Nette\Kdyby\NodeManipulator\ListeningClassMethodArgumentManipulator
     */
    private $listeningClassMethodArgumentManipulator;
    /**
     * @readonly
     * @var \Rector\Nette\Kdyby\NodeResolver\ListeningMethodsCollector
     */
    private $listeningMethodsCollector;
    /**
     * @readonly
     * @var \Rector\Nette\Kdyby\NodeAnalyzer\GetSubscribedEventsClassMethodAnalyzer
     */
    private $getSubscribedEventsClassMethodAnalyzer;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(\Rector\Nette\Kdyby\NodeManipulator\GetSubscribedEventsArrayManipulator $getSubscribedEventsArrayManipulator, \Rector\Nette\Kdyby\NodeManipulator\ListeningClassMethodArgumentManipulator $listeningClassMethodArgumentManipulator, \Rector\Nette\Kdyby\NodeResolver\ListeningMethodsCollector $listeningMethodsCollector, \Rector\Nette\Kdyby\NodeAnalyzer\GetSubscribedEventsClassMethodAnalyzer $getSubscribedEventsClassMethodAnalyzer, \Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator)
    {
        $this->getSubscribedEventsArrayManipulator = $getSubscribedEventsArrayManipulator;
        $this->listeningClassMethodArgumentManipulator = $listeningClassMethodArgumentManipulator;
        $this->listeningMethodsCollector = $listeningMethodsCollector;
        $this->getSubscribedEventsClassMethodAnalyzer = $getSubscribedEventsClassMethodAnalyzer;
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change EventSubscriber from Kdyby to Contributte', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Kdyby\Events\Subscriber;
use Nette\Application\Application;
use Nette\Application\UI\Presenter;

class GetApplesSubscriber implements Subscriber
{
    public function getSubscribedEvents()
    {
        return [
            Application::class . '::onShutdown',
        ];
    }

    public function onShutdown(Presenter $presenter)
    {
        $presenterName = $presenter->getName();
        // ...
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Contributte\Events\Extra\Event\Application\ShutdownEvent;
use Kdyby\Events\Subscriber;
use Nette\Application\Application;

class GetApplesSubscriber implements Subscriber
{
    public static function getSubscribedEvents()
    {
        return [
            ShutdownEvent::class => 'onShutdown',
        ];
    }

    public function onShutdown(ShutdownEvent $shutdownEvent)
    {
        $presenter = $shutdownEvent->getPresenter();
        $presenterName = $presenter->getName();
        // ...
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->getSubscribedEventsClassMethodAnalyzer->detect($node)) {
            return null;
        }
        $this->visibilityManipulator->makeStatic($node);
        $this->refactorEventNames($node);
        $listeningClassMethods = $this->listeningMethodsCollector->collectFromClassAndGetSubscribedEventClassMethod($node, \Rector\Nette\Kdyby\NodeResolver\ListeningMethodsCollector::EVENT_TYPE_CONTRIBUTTE);
        $this->listeningClassMethodArgumentManipulator->change($listeningClassMethods);
        return $node;
    }
    private function refactorEventNames(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) {
            if (!$node instanceof \PhpParser\Node\Stmt\Return_) {
                return null;
            }
            if ($node->expr === null) {
                return null;
            }
            $returnedExpr = $node->expr;
            if (!$returnedExpr instanceof \PhpParser\Node\Expr\Array_) {
                return null;
            }
            $this->refactorArrayWithEventTable($returnedExpr);
            $this->getSubscribedEventsArrayManipulator->change($returnedExpr);
        });
    }
    private function refactorArrayWithEventTable(\PhpParser\Node\Expr\Array_ $array) : void
    {
        foreach ($array->items as $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }
            if ($arrayItem->key !== null) {
                continue;
            }
            $methodName = $this->resolveMethodNameFromKdybyEventName($arrayItem->value);
            $arrayItem->key = $arrayItem->value;
            $arrayItem->value = new \PhpParser\Node\Scalar\String_($methodName);
        }
    }
    private function resolveMethodNameFromKdybyEventName(\PhpParser\Node\Expr $expr) : string
    {
        $kdybyEventName = $this->valueResolver->getValue($expr);
        if (!\is_string($kdybyEventName)) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if (\strpos($kdybyEventName, '::') !== \false) {
            return (string) \RectorPrefix20220501\Nette\Utils\Strings::after($kdybyEventName, '::', -1);
        }
        throw new \Rector\Core\Exception\NotImplementedYetException($kdybyEventName);
    }
}
