<?php

declare (strict_types=1);
namespace Rector\Naming\Guard;

use DateTimeInterface;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Util\StringUtils;
use Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
/**
 * @implements ConflictingNameGuardInterface<PropertyRename>
 */
final class DateTimeAtNamingConventionGuard implements ConflictingNameGuardInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper
     */
    private $typeUnwrapper;
    public function __construct(NodeTypeResolver $nodeTypeResolver, TypeUnwrapper $typeUnwrapper)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeUnwrapper = $typeUnwrapper;
    }
    /**
     * @param PropertyRename $renameValueObject
     */
    public function isConflicting(RenameValueObjectInterface $renameValueObject) : bool
    {
        return $this->isDateTimeAtNamingConvention($renameValueObject);
    }
    private function isDateTimeAtNamingConvention(PropertyRename $propertyRename) : bool
    {
        $type = $this->nodeTypeResolver->getType($propertyRename->getProperty());
        $type = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($type);
        if (!$type instanceof TypeWithClassName) {
            return \false;
        }
        if (!\is_a($type->getClassName(), DateTimeInterface::class, \true)) {
            return \false;
        }
        return StringUtils::isMatch($propertyRename->getCurrentName(), \Rector\Naming\Guard\BreakingVariableRenameGuard::AT_NAMING_REGEX . '');
    }
}
