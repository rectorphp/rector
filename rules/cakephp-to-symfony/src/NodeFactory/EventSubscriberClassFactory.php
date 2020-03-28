<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeFactory;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

final class EventSubscriberClassFactory
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    public function __construct(BuilderFactory $builderFactory)
    {
        $this->builderFactory = $builderFactory;
    }

    /**
     * @return Namespace_|Class_
     */
    public function createEventSubscriberClass(Class_ $class, ClassMethod $onBeforeFilterClassMethod): Node
    {
        $eventSubscriberClassName = $this->createEventSubscriberClassName($class);

        $classBuilder = $this->builderFactory->class($eventSubscriberClassName);
        $classBuilder->implement(new FullyQualified('Symfony\Component\EventDispatcher\EventSubscriberInterface'));
        $classBuilder->makeFinal();

        $classBuilder->addStmt($this->createGetSubscribedEventsClassMethod());
        $classBuilder->addStmt($this->createOnKernelRequestClassMethod($onBeforeFilterClassMethod));

        $eventSubscriberClass = $classBuilder->getNode();

        /** @var string|null $namespaceName */
        $namespaceName = $class->getAttribute(AttributeKey::NAMESPACE_NAME);
        if ($namespaceName === null) {
            return $eventSubscriberClass;
        }

        $namespace = new Namespace_(new Name($namespaceName));
        $namespace->stmts[] = $eventSubscriberClass;

        return $namespace;
    }

    public function resolveEventSubscriberFilePath(Class_ $class): string
    {
        /** @var SmartFileInfo $fileInfo */
        $fileInfo = $class->getAttribute(AttributeKey::FILE_INFO);
        $eventSubscriberClassName = $this->createEventSubscriberClassName($class);
        return dirname($fileInfo->getRealPath()) . DIRECTORY_SEPARATOR . $eventSubscriberClassName . '.php';
    }

    private function createEventSubscriberClassName(Class_ $class): string
    {
        $className = $class->getAttribute(AttributeKey::CLASS_SHORT_NAME);

        return $className . 'EventSubscriber';
    }

    private function createGetSubscribedEventsClassMethod(): ClassMethod
    {
        $classMethodBuilder = $this->builderFactory->method('getSubscribedEvents');
        $classMethodBuilder->makePublic();
        $classMethodBuilder->makeStatic();

        $eventConstant = new ClassConstFetch(new FullyQualified(
            'Symfony\Component\HttpKernel\KernelEvents'
        ), 'REQUEST');
        $arrayItem = new ArrayItem(new String_('onKernelRequest'), $eventConstant);
        $eventsToMethodsArray = new Array_([$arrayItem]);

        $return = new Return_($eventsToMethodsArray);
        $classMethodBuilder->addStmt($return);
        $classMethodBuilder->setReturnType('array');

        return $classMethodBuilder->getNode();
    }

    private function createOnKernelRequestClassMethod(ClassMethod $onBeforeFilterClassMethod): ClassMethod
    {
        $classMethodBuilder = $this->builderFactory->method('onKernelRequest');
        $classMethodBuilder->addStmts((array) $onBeforeFilterClassMethod->stmts);
        $classMethodBuilder->makePublic();

        return $classMethodBuilder->getNode();
    }
}
