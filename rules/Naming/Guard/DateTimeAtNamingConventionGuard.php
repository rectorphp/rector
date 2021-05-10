<?php

declare(strict_types=1);

namespace Rector\Naming\Guard;

use DateTimeInterface;
use Nette\Utils\Strings;
use PHPStan\Type\TypeWithClassName;
use Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

final class DateTimeAtNamingConventionGuard implements ConflictingNameGuardInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/1pKLgf/1/
     */
    private const AT_NAMING_REGEX = '#[\w+]At$#';

    public function __construct(
        private NodeTypeResolver $nodeTypeResolver,
        private TypeUnwrapper $typeUnwrapper
    ) {
    }

    /**
     * @param PropertyRename $renameValueObject
     */
    public function isConflicting(RenameValueObjectInterface $renameValueObject): bool
    {
        return $this->isDateTimeAtNamingConvention($renameValueObject);
    }

    private function isDateTimeAtNamingConvention(PropertyRename $propertyRename): bool
    {
        $type = $this->nodeTypeResolver->resolve($propertyRename->getProperty());
        $type = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($type);

        if (! $type instanceof TypeWithClassName) {
            return false;
        }

        if (! is_a($type->getClassName(), DateTimeInterface::class, true)) {
            return false;
        }

        return (bool) Strings::match($propertyRename->getCurrentName(), self::AT_NAMING_REGEX . '');
    }
}
