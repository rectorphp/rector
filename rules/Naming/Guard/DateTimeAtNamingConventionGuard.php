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
final class DateTimeAtNamingConventionGuard implements \Rector\Naming\Contract\Guard\ConflictingNameGuardInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/1pKLgf/1/
     */
    private const AT_NAMING_REGEX = '#[\\w+]At$#';
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
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper $typeUnwrapper)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeUnwrapper = $typeUnwrapper;
    }
    /**
     * @param PropertyRename $renameValueObject
     */
    public function isConflicting(\Rector\Naming\Contract\RenameValueObjectInterface $renameValueObject) : bool
    {
        return $this->isDateTimeAtNamingConvention($renameValueObject);
    }
    private function isDateTimeAtNamingConvention(\Rector\Naming\ValueObject\PropertyRename $propertyRename) : bool
    {
        $type = $this->nodeTypeResolver->getType($propertyRename->getProperty());
        $type = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($type);
        if (!$type instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        if (!\is_a($type->getClassName(), \DateTimeInterface::class, \true)) {
            return \false;
        }
        return \Rector\Core\Util\StringUtils::isMatch($propertyRename->getCurrentName(), self::AT_NAMING_REGEX . '');
    }
}
