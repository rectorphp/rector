<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\DataProvider;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeCollector\NodeCollector\NodeRepository;

final class GetSubscribedEventsClassMethodProvider
{
    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @return ClassMethod[]
     */
    public function provide(): array
    {
        return $this->nodeRepository->findClassMethodByTypeAndMethod(
            'Kdyby\Events\Subscriber',
            'getSubscribedEvents'
        );
    }
}
