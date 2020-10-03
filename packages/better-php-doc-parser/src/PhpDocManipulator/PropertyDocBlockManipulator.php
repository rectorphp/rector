<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\ValueObject\RenameValueObjectInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyDocBlockManipulator
{
    /**
     * @param ParamRename $renameValueObject
     */
    public function renameParameterNameInDocBlock(RenameValueObjectInterface $renameValueObject): void
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $renameValueObject->getFunctionLike()
            ->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        $paramTagValueNode = $phpDocInfo->getParamTagValueNodeByName($renameValueObject->getCurrentName());
        if ($paramTagValueNode === null) {
            return;
        }

        $paramTagValueNode->parameterName = '$' . $renameValueObject->getExpectedName();
    }
}
