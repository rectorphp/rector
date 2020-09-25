<?php

declare(strict_types=1);

namespace Rector\Naming\Guard;

use DateTimeInterface;
use Nette\Utils\Strings;
use PHPStan\Type\TypeWithClassName;
use Rector\Naming\ValueObject\RenameValueObjectInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

class DateTimeAtNamingConventionGuard implements GuardInterface
{
    /**
     * @var string
     */
    private const AT_NAMING_REGEX = '#[\w+]At$#';

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    public function __construct(NodeTypeResolver $nodeTypeResolver, TypeUnwrapper $typeUnwrapper)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeUnwrapper = $typeUnwrapper;
    }

    public function check(RenameValueObjectInterface $renameValueObject): bool
    {
        return $this->isDateTimeAtNamingConvention($renameValueObject);
    }

    protected function isDateTimeAtNamingConvention(RenameValueObjectInterface $renameValueObject): bool
    {
        $type = $this->nodeTypeResolver->resolve($renameValueObject->getNode());
        $type = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($type);
        if (! $type instanceof TypeWithClassName) {
            return false;
        }

        if (! is_a($type->getClassName(), DateTimeInterface::class, true)) {
            return false;
        }

        return (bool) Strings::match($renameValueObject->getCurrentName(), self::AT_NAMING_REGEX . '');
    }
}
