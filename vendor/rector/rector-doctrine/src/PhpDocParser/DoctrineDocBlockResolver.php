<?php

declare (strict_types=1);
namespace Rector\Doctrine\PhpDocParser;

use RectorPrefix202308\Doctrine\ORM\Mapping\Entity;
use RectorPrefix202308\Doctrine\ORM\Mapping\Embeddable;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
final class DoctrineDocBlockResolver
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function isDoctrineEntityClass(Class_ $class) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        return $phpDocInfo->hasByAnnotationClasses([Entity::class, Embeddable::class]);
    }
}
