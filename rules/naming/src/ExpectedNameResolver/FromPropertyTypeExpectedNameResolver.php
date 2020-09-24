<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;

class FromPropertyTypeExpectedNameResolver extends AbstractExpectedNameResolver
{
    public function resolveIfNotYet(Property $property): ?string
    {
        $expectedName = $this->resolve($property);
        if ($expectedName === null) {
            return null;
        }

        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);
        if ($this->endsWith($propertyName, $expectedName)) {
            return null;
        }

        if ($this->nodeNameResolver->isName($property, $expectedName)) {
            return null;
        }

        return $expectedName;
    }

    public function resolve(Property $property): ?string
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        if ($this->nodeTypeResolver->isPropertyBoolean($property)) {
            return $this->propertyNaming->getExpectedNameFromBooleanPropertyType($property);
        }

        $expectedName = $this->propertyNaming->getExpectedNameFromType($phpDocInfo->getVarType());
        if ($expectedName === null) {
            return null;
        }

        return $expectedName->getName();
    }

    /**
     * Ends with ucname
     * Starts with adjective, e.g. (Post $firstPost, Post $secondPost)
     */
    private function endsWith(string $currentName, string $expectedName): bool
    {
        $suffixNamePattern = '#\w+' . ucfirst($expectedName) . '#';
        return (bool) Strings::match($currentName, $suffixNamePattern);
    }
}
