<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypeAnalyzer;

use RectorPrefix202308\Doctrine\ORM\Mapping\OneToMany;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
final class CollectionVarTagValueNodeResolver
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
    public function resolve(Property $property) : ?VarTagValueNode
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if (!$phpDocInfo->hasByAnnotationClass(OneToMany::class)) {
            return null;
        }
        return $phpDocInfo->getVarTagValueNode();
    }
}
