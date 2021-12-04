<?php

declare(strict_types=1);

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
    public function __construct(
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly ParamTagRemover $paramTagRemover,
    ) {
    }

    /**
     * @param PropertyWithPhpDocInfo[] $requiredPropertiesWithPhpDocInfos
     * @param Param[] $params
     */
    public function createConstructorClassMethod(array $requiredPropertiesWithPhpDocInfos, array $params): ClassMethod
    {
        $classMethod = new ClassMethod(MethodName::CONSTRUCT, [
            'flags' => Class_::MODIFIER_PUBLIC,
            'params' => $params,
        ]);

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        foreach ($requiredPropertiesWithPhpDocInfos as $requiredPropertyWithPhpDocInfo) {
            $paramTagValueNode = $requiredPropertyWithPhpDocInfo->getParamTagValueNode();
            $phpDocInfo->addTagValueNode($paramTagValueNode);
        }

        $this->paramTagRemover->removeParamTagsIfUseless($phpDocInfo, $classMethod);

        return $classMethod;
    }
}
