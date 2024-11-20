<?php

declare (strict_types=1);
namespace Rector\Naming\ParamRenamer;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\VariableRenamer;
final class ParamRenamer
{
    /**
     * @readonly
     */
    private VariableRenamer $variableRenamer;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(VariableRenamer $variableRenamer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->variableRenamer = $variableRenamer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function rename(ParamRename $paramRename) : void
    {
        // 1. rename param
        $paramRename->getVariable()->name = $paramRename->getExpectedName();
        // 2. rename param in the rest of the method
        $this->variableRenamer->renameVariableInFunctionLike($paramRename->getFunctionLike(), $paramRename->getCurrentName(), $paramRename->getExpectedName(), null);
        // 3. rename @param variable in docblock too
        $this->renameParameterNameInDocBlock($paramRename);
    }
    private function renameParameterNameInDocBlock(ParamRename $paramRename) : void
    {
        $functionLike = $paramRename->getFunctionLike();
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($functionLike);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramRename->getCurrentName());
        if (!$paramTagValueNode instanceof ParamTagValueNode) {
            return;
        }
        $paramTagValueNode->parameterName = '$' . $paramRename->getExpectedName();
        $paramTagValueNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($functionLike);
    }
}
