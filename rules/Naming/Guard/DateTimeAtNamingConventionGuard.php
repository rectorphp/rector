<?php

declare (strict_types=1);
namespace Rector\Naming\Guard;

use DateTimeInterface;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
use Rector\Util\StringUtils;
final class DateTimeAtNamingConventionGuard
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private TypeUnwrapper $typeUnwrapper;
    public function __construct(NodeTypeResolver $nodeTypeResolver, TypeUnwrapper $typeUnwrapper)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeUnwrapper = $typeUnwrapper;
    }
    public function isConflicting(PropertyRename $propertyRename) : bool
    {
        $type = $this->nodeTypeResolver->getType($propertyRename->getProperty());
        $type = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($type);
        $className = ClassNameFromObjectTypeResolver::resolve($type);
        if ($className === null) {
            return \false;
        }
        if (!\is_a($className, DateTimeInterface::class, \true)) {
            return \false;
        }
        return StringUtils::isMatch($propertyRename->getCurrentName(), \Rector\Naming\Guard\BreakingVariableRenameGuard::AT_NAMING_REGEX);
    }
}
