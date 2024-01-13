<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypeAnalyzer;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Doctrine\CodeQuality\Enum\ToManyMappings;
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
        if (!$phpDocInfo->hasByAnnotationClasses(ToManyMappings::TO_MANY_CLASSES)) {
            return null;
        }
        return $phpDocInfo->getVarTagValueNode();
    }
}
