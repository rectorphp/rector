<?php

declare (strict_types=1);
namespace Rector\Compatibility\NodeFactory;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Compatibility\ValueObject\PropertyWithPhpDocInfo;
use Rector\Core\ValueObject\MethodName;
use Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
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
