<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Compatibility\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use RectorPrefix20220606\Rector\Compatibility\ValueObject\PropertyWithPhpDocInfo;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
final class ConstructorClassMethodFactory
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover
     */
    private $paramTagRemover;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ParamTagRemover $paramTagRemover)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->paramTagRemover = $paramTagRemover;
    }
    /**
     * @param PropertyWithPhpDocInfo[] $requiredPropertiesWithPhpDocInfos
     * @param Param[] $params
     */
    public function createConstructorClassMethod(array $requiredPropertiesWithPhpDocInfos, array $params) : ClassMethod
    {
        $classMethod = new ClassMethod(MethodName::CONSTRUCT, ['flags' => Class_::MODIFIER_PUBLIC, 'params' => $params]);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        foreach ($requiredPropertiesWithPhpDocInfos as $requiredPropertyWithPhpDocInfo) {
            $paramTagValueNode = $requiredPropertyWithPhpDocInfo->getParamTagValueNode();
            $phpDocInfo->addTagValueNode($paramTagValueNode);
        }
        $this->paramTagRemover->removeParamTagsIfUseless($phpDocInfo, $classMethod);
        return $classMethod;
    }
}
