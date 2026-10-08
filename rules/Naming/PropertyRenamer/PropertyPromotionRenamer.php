<?php

declare (strict_types=1);
namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Naming\ParamRenamer\ParamRenamer;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\ValueObjectFactory\ParamRenameFactory;
final class PropertyPromotionRenamer
{
    /**
     * @readonly
     */
    private ParamRenameFactory $paramRenameFactory;
    /**
     * @readonly
     */
    private ParamRenamer $paramRenamer;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(ParamRenameFactory $paramRenameFactory, ParamRenamer $paramRenamer, DocBlockUpdater $docBlockUpdater)
    {
        $this->paramRenameFactory = $paramRenameFactory;
        $this->paramRenamer = $paramRenamer;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function renameParamDoc(PhpDocInfo $phpDocInfo, ClassMethod $classMethod, Param $param, string $paramVarName, string $desiredPropertyName): void
    {
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramVarName);
        if (!$paramTagValueNode instanceof ParamTagValueNode) {
            return;
        }
        $paramRename = $this->paramRenameFactory->createFromResolvedExpectedName($classMethod, $param, $desiredPropertyName);
        if (!$paramRename instanceof ParamRename) {
            return;
        }
        $this->paramRenamer->rename($paramRename);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
    }
}
