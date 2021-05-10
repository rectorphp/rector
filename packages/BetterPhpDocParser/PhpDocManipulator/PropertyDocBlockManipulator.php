<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ValueObject\ParamRename;
final class PropertyDocBlockManipulator
{
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @param ParamRename $renameValueObject
     */
    public function renameParameterNameInDocBlock(\Rector\Naming\Contract\RenameValueObjectInterface $renameValueObject) : void
    {
        $functionLike = $renameValueObject->getFunctionLike();
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $paramTagValueNode = $phpDocInfo->getParamTagValueNodeByName($renameValueObject->getCurrentName());
        if (!$paramTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode) {
            return;
        }
        $paramTagValueNode->parameterName = '$' . $renameValueObject->getExpectedName();
        $paramTagValueNode->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::ORIG_NODE, null);
    }
}
