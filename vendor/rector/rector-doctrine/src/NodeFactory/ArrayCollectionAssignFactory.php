<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\NodeFactory;
final class ArrayCollectionAssignFactory
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function createFromPropertyName(string $toManyPropertyName) : \PhpParser\Node\Stmt\Expression
    {
        $propertyFetch = $this->nodeFactory->createPropertyFetch('this', $toManyPropertyName);
        $new = new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified('Doctrine\\Common\\Collections\\ArrayCollection'));
        $assign = new \PhpParser\Node\Expr\Assign($propertyFetch, $new);
        return new \PhpParser\Node\Stmt\Expression($assign);
    }
}
