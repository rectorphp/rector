<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MatchPropertyTypeExpectedNameResolver extends AbstractExpectedNameResolver
{
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @required
     */
    public function autowireMatchPropertyTypeExpectedNameResolver(PropertyNaming $propertyNaming): void
    {
        $this->propertyNaming = $propertyNaming;
    }

    public function resolve(Node $node): ?string
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        $expectedName = $this->propertyNaming->getExpectedNameFromType($phpDocInfo->getVarType());
        if ($expectedName === null) {
            return null;
        }

        return $expectedName->getName();
    }
}
