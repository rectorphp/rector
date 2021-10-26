<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\NodeNameResolver\NodeNameResolver;

final class MatchPropertyTypeExpectedNameResolver
{
    public function __construct(
        private PropertyNaming $propertyNaming,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function resolve(Property $property): ?string
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $expectedName = $this->propertyNaming->getExpectedNameFromType($phpDocInfo->getVarType());
        if (! $expectedName instanceof ExpectedName) {
            return null;
        }

        // skip if already has suffix
        $currentName = $this->nodeNameResolver->getName($property);
        if ($this->nodeNameResolver->endsWith($currentName, $expectedName->getName())) {
            return null;
        }

        return $expectedName->getName();
    }
}
