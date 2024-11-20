<?php

declare (strict_types=1);
namespace Rector\Removing\NodeManipulator;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\NodeNameResolver\NodeNameResolver;
final class ComplexNodeRemover
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PhpDocTagRemover $phpDocTagRemover;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocTagRemover $phpDocTagRemover, NodeNameResolver $nodeNameResolver, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    /**
     * @param int[] $paramKeysToBeRemoved
     * @return int[]
     */
    public function processRemoveParamWithKeys(ClassMethod $classMethod, array $paramKeysToBeRemoved) : array
    {
        $totalKeys = \count($classMethod->params) - 1;
        $removedParamKeys = [];
        $phpdocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        foreach ($paramKeysToBeRemoved as $paramKeyToBeRemoved) {
            $startNextKey = $paramKeyToBeRemoved + 1;
            for ($nextKey = $startNextKey; $nextKey <= $totalKeys; ++$nextKey) {
                if (!isset($classMethod->params[$nextKey])) {
                    // no next param, break the inner loop, remove the param
                    break;
                }
                if (\in_array($nextKey, $paramKeysToBeRemoved, \true)) {
                    // keep searching next key not in $paramKeysToBeRemoved
                    continue;
                }
                return [];
            }
            $paramName = (string) $this->nodeNameResolver->getName($classMethod->params[$paramKeyToBeRemoved]);
            unset($classMethod->params[$paramKeyToBeRemoved]);
            $paramTagValueByName = $phpdocInfo->getParamTagValueByName($paramName);
            if ($paramTagValueByName instanceof ParamTagValueNode) {
                $this->phpDocTagRemover->removeTagValueFromNode($phpdocInfo, $paramTagValueByName);
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
            }
            $removedParamKeys[] = $paramKeyToBeRemoved;
        }
        return $removedParamKeys;
    }
}
