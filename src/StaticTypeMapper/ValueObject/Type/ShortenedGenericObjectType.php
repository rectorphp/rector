<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\RelativeNameNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IsSuperTypeOfResult;
use PHPStan\Type\Type;
/**
 * @api
 */
final class ShortenedGenericObjectType extends GenericObjectType
{
    /**
     * @var class-string
     * @readonly
     */
    private string $fullyQualifiedName;
    /**
     * @param class-string $fullyQualifiedName
     */
    public function __construct(string $shortName, array $types, string $fullyQualifiedName)
    {
        $this->fullyQualifiedName = $fullyQualifiedName;
        parent::__construct($shortName, $types);
    }
    public function isSuperTypeOf(Type $type): IsSuperTypeOfResult
    {
        $genericObjectType = new GenericObjectType($this->fullyQualifiedName, $this->getTypes());
        return $genericObjectType->isSuperTypeOf($type);
    }
    public function getShortName(): string
    {
        return $this->getClassName();
    }
    public function toPhpDocNode(): TypeNode
    {
        $typeNode = parent::toPhpDocNode();
        if ($typeNode instanceof GenericTypeNode) {
            $typeNode->type = new RelativeNameNode($this->getShortName());
        }
        return $typeNode;
    }
}
