<?php

declare (strict_types=1);
namespace Rector\Nette\NeonParser;

use Rector\Nette\NeonParser\NodeFactory\ServiceFactory;
use Rector\Nette\NeonParser\Services\ServiceTypeResolver;
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
    public function __construct(\Rector\Nette\NeonParser\Services\ServiceTypeResolver $serviceTypeResolver, \Rector\Nette\NeonParser\NodeFactory\ServiceFactory $serviceFactory)
    {
        $this->serviceTypeResolver = $serviceTypeResolver;
        $this->serviceFactory = $serviceFactory;
    }
    public function create() : \Rector\Nette\NeonParser\NeonNodeTraverser
    {
        return new \Rector\Nette\NeonParser\NeonNodeTraverser($this->serviceTypeResolver, $this->serviceFactory);
    }
}
