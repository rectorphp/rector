<?php

declare (strict_types=1);
namespace Rector\Doctrine\PhpDocParser;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class DoctrineDocBlockResolver
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, BetterNodeFinder $betterNodeFinder)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isInDoctrineEntityClass(Node $node) : bool
    {
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return \false;
        }
        return $this->isDoctrineEntityClass($class);
    }
    private function isDoctrineEntityClass(Class_ $class) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        return $phpDocInfo->hasByAnnotationClasses(['Doctrine\\ORM\\Mapping\\Entity', 'Doctrine\\ORM\\Mapping\\Embeddable']);
    }
}
