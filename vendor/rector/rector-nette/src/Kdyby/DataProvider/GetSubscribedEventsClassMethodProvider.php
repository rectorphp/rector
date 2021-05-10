<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\DataProvider;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeCollector\NodeCollector\NodeRepository;
final class GetSubscribedEventsClassMethodProvider
{
    /**
     * @var NodeRepository
     */
    private $nodeRepository;
    public function __construct(\Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }
    /**
     * @return ClassMethod[]
     */
    public function provide() : array
    {
        $subscriberClasses = $this->nodeRepository->findClassesAndInterfacesByType('Kdyby\\Events\\Subscriber');
        $classMethods = [];
        foreach ($subscriberClasses as $subscriberClass) {
            $subscribedEventsClassMethod = $subscriberClass->getMethod('getSubscribedEvents');
            if ($subscribedEventsClassMethod === null) {
                continue;
            }
            $classMethods[] = $subscribedEventsClassMethod;
        }
        return $classMethods;
    }
}
