<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\NeonParser;

use RectorPrefix20220606\Rector\Nette\NeonParser\NodeFactory\ServiceFactory;
use RectorPrefix20220606\Rector\Nette\NeonParser\Services\ServiceTypeResolver;
final class NeonNodeTraverserFactory
{
    /**
     * @var \Rector\Nette\NeonParser\Services\ServiceTypeResolver
     */
    private $serviceTypeResolver;
    /**
     * @var \Rector\Nette\NeonParser\NodeFactory\ServiceFactory
     */
    private $serviceFactory;
    public function __construct(ServiceTypeResolver $serviceTypeResolver, ServiceFactory $serviceFactory)
    {
        $this->serviceTypeResolver = $serviceTypeResolver;
        $this->serviceFactory = $serviceFactory;
    }
    public function create() : NeonNodeTraverser
    {
        return new NeonNodeTraverser($this->serviceTypeResolver, $this->serviceFactory);
    }
}
