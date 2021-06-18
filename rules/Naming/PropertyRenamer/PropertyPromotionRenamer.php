<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Rector\Naming\ParamRenamer\ParamRenamer;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\ValueObjectFactory\ParamRenameFactory;
use Rector\NodeNameResolver\NodeNameResolver;

final class PropertyPromotionRenamer
{
    public function __construct(
        private PhpVersionProvider $phpVersionProvider,
        private MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver,
        private ParamRenameFactory $paramRenameFactory,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private ParamRenamer $paramRenamer,
        private PropertyFetchRenamer $propertyFetchRenamer,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function renamePropertyPromotion(ClassLike $classLike): void
    {
        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
            return;
        }

        $constructClassMethod = $classLike->getMethod(MethodName::CONSTRUCT);
        if (! $constructClassMethod instanceof ClassMethod) {
            return;
        }

        // resolve possible and existing param names
        $blockingParamNames = $this->resolveBlockingParamNames($constructClassMethod);

        foreach ($constructClassMethod->params as $param) {
            if ($param->flags === 0) {
                continue;
            }

            // promoted property
            $desiredPropertyName = $this->matchParamTypeExpectedNameResolver->resolve($param);
            if ($desiredPropertyName === null) {
                continue;
            }

            if (in_array($desiredPropertyName, $blockingParamNames, true)) {
                continue;
            }

            $currentParamName = $this->nodeNameResolver->getName($param);
            if ($this->isNameSuffixed($currentParamName, $desiredPropertyName)) {
                continue;
            }

            $this->renameParamVarName($classLike, $constructClassMethod, $desiredPropertyName, $param);
        }
    }

    private function renameParamVarName(
        ClassLike $classLike,
        ClassMethod $classMethod,
        string $desiredPropertyName,
        Param $param
    ): void {
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        $currentParamName = $this->nodeNameResolver->getName($param);

        $this->propertyFetchRenamer->renamePropertyFetchesInClass(
            $classLike,
            $currentParamName,
            $desiredPropertyName
        );

        /** @var string $paramVarName */
        $paramVarName = $param->var->name;
        $this->renameParamDoc($classMethodPhpDocInfo, $param, $paramVarName, $desiredPropertyName);
        $param->var->name = $desiredPropertyName;
    }

    private function renameParamDoc(
        PhpDocInfo $phpDocInfo,
        Param $param,
        string $paramVarName,
        string $desiredPropertyName
    ): void {
        $paramTagValueNode = $phpDocInfo->getParamTagValueNodeByName($paramVarName);
        if (! $paramTagValueNode instanceof ParamTagValueNode) {
            return;
        }

        $paramRename = $this->paramRenameFactory->createFromResolvedExpectedName($param, $desiredPropertyName);
        if (! $paramRename instanceof ParamRename) {
            return;
        }

        $this->paramRenamer->rename($paramRename);
    }

    /**
     * Sometimes the bare type is not enough.
     * This allows prefixing type in variable names, e.g. "Type $firstType"
     */
    private function isNameSuffixed(string $currentParamName, string $desiredPropertyName): bool
    {
        $currentNameLowercased = strtolower($currentParamName);
        $expectedNameLowercased = strtolower($desiredPropertyName);

        return str_ends_with($currentNameLowercased, $expectedNameLowercased);
    }

    /**
     * @return int[]|string[]
     */
    private function resolveBlockingParamNames(ClassMethod $classMethod): array
    {
        $futureParamNames = [];
        foreach ($classMethod->params as $param) {
            $futureParamName = $this->matchParamTypeExpectedNameResolver->resolve($param);
            if ($futureParamName === null) {
                continue;
            }

            $futureParamNames[] = $futureParamName;
        }

        // remove null values
        $futureParamNames = array_filter($futureParamNames);
        if ($futureParamNames === []) {
            return [];
        }

        // resolve duplicated names
        $blockingParamNames = [];

        $valuesToCount = array_count_values($futureParamNames);
        foreach ($valuesToCount as $value => $count) {
            if ($count < 2) {
                continue;
            }

            $blockingParamNames[] = $value;
        }

        return $blockingParamNames;
    }
}
