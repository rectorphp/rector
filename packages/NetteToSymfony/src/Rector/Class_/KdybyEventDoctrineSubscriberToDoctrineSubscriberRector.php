<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class KdybyEventDoctrineSubscriberToDoctrineSubscriberRector extends AbstractRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var string
     */
    private $symfonyEventSubscriberInterface = 'Symfony\Component\EventDispatcher\EventSubscriberInterface';

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        ClassManipulator $classManipulator,
        BetterNodeFinder $betterNodeFinder,
        NodeFactory $nodeFactory
    ) {
        $this->classManipulator = $classManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeFactory = $nodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove empty constructor', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Kdyby\Doctrine\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SomeDoctrineEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
         return [
             Events::postPersist => 'firstFunction'
         ];
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Kdyby\Doctrine\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SomeDoctrineEventSubscriber implements \Doctrine\Common\EventSubscriber
{
    public function getSubscribedEvents()
    {
         return [\Doctrine\ORM\Events::postPersist];
    }

    public function postPersist(LifecycleEventArgs $event): void
    {
        $this->firstFunction($event);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, $this->symfonyEventSubscriberInterface)) {
            return null;
        }

        // is event subscriber with Doctrine events?
        $getSubscribedEventClassMethod = $this->classManipulator->getMethodByName($node, 'getSubscribedEvents');
        if ($getSubscribedEventClassMethod === null) {
            return null;
        }

        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf($getSubscribedEventClassMethod, Return_::class);
        if (! $this->isKdybyDoctrineEventSubscriber($returns)) {
            return null;
        }

        $this->processImplements($node);

        // change "getSubscribedEvents" from Symfony to Doctrine event subscriber
        $this->makeNonStatic($getSubscribedEventClassMethod);

        $eventToMethodMap = $this->processArrayKeysAndCollectEventToMethodMap($returns);

        // turn calls to new methods
        $extraClassMethods = [];
        foreach ($eventToMethodMap as $eventName => $methodName) {
            $extraClassMethods[] = $this->createEventDelegatingClassMethod($eventName, $methodName);
        }

        $node->stmts = array_merge($node->stmts, $extraClassMethods);

        return $node;
    }

    /**
     * @param Return_[] $returns
     */
    private function isKdybyDoctrineEventSubscriber(array $returns): bool
    {
        foreach ($returns as $return) {
            if (! $return->expr instanceof Array_) {
                continue;
            }

            foreach ($return->expr->items as $arrayItem) {
                if ($arrayItem->key === null) {
                    return false;
                }

                // key of array must be doctrine event from Kdyby
                $keyValue = $this->getValue($arrayItem->key);
                if (Strings::startsWith($keyValue, 'Kdyby\Doctrine\Events')) {
                    return true;
                }
            }
        }

        return false;
    }

    private function createEventDelegatingClassMethod(string $eventName, string $methodName): ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod($eventName);

        if ($this->isAtLeastPhpVersion('7.1')) {
            $classMethod->returnType = new Identifier('void');
        }

        $eventVariable = new Variable('event');
        $param = new Param($eventVariable, null, new FullyQualified('Doctrine\ORM\Event\LifecycleEventArgs'));
        $classMethod->params[] = $param;

        $methodCall = new MethodCall(new Variable('this'), $methodName, [new Arg($eventVariable)]);
        $classMethod->stmts[] = new Expression($methodCall);

        return $classMethod;
    }

    private function processImplements(Class_ $class): void
    {
        foreach ($class->implements as $key => $implement) {
            if (! $this->isName($implement, $this->symfonyEventSubscriberInterface)) {
                continue;
            }

            $class->implements[$key] = new FullyQualified('Doctrine\Common\EventSubscriber');
            break;
        }
    }

    /**
     * @param Return_[] $returns
     * @return string[]
     */
    private function processArrayKeysAndCollectEventToMethodMap(array $returns): array
    {
        $eventToMethodMap = [];

        foreach ($returns as $return) {
            if (! $return->expr instanceof Array_) {
                continue;
            }

            foreach ($return->expr->items as $arrayItem) {
                if ($arrayItem->key === null) {
                    continue;
                }

                if (! $arrayItem->key instanceof ClassConstFetch) {
                    continue;
                }

                $keyValue = $this->getValue($arrayItem->key);
                if (! Strings::startsWith($keyValue, 'Kdyby\Doctrine\Events')) {
                    continue;
                }

                $arrayItem->key->class = new FullyQualified('Doctrine\ORM\Events');

                $eventToMethodMap[$this->getName($arrayItem->key->name)] = $this->getValue($arrayItem->value);

                // make key to value
                $arrayItem->value = $arrayItem->key;
                $arrayItem->key = null;
            }
        }

        return $eventToMethodMap;
    }
}
