<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;

final class DoctrineClassAnalyzer
{
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function matchDoctrineEntityTagValueNode(Class_ $class): ?EntityTagValueNode
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        return $phpDocInfo->getByType(EntityTagValueNode::class);
    }
}
