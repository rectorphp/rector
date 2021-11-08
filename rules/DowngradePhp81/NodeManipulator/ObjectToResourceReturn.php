<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\NodeManipulator;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\PhpParser\Node\NodeFactory;
final class ObjectToResourceReturn
{
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param string[] $collectionObjectToResource
     */
    public function refactor(\PhpParser\Node\Expr\Instanceof_ $instanceof, array $collectionObjectToResource) : ?\PhpParser\Node\Expr\FuncCall
    {
        if (!$instanceof->class instanceof \PhpParser\Node\Name\FullyQualified) {
            return null;
        }
        $className = $instanceof->class->toString();
        foreach ($collectionObjectToResource as $singleCollectionObjectToResource) {
            if ($singleCollectionObjectToResource === $className) {
                return $this->nodeFactory->createFuncCall('is_resource', [$instanceof->expr]);
            }
        }
        return null;
    }
}
