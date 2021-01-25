<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use DateTimeInterface;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;

final class DoctrineColumnPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var Type[]
     *
     * @see \Doctrine\DBAL\Platforms\MySqlPlatform::initializeDoctrineTypeMappings()
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html#doctrine-mapping-types
     */
    private $doctrineTypeToScalarType = [];

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(TypeFactory $typeFactory, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->typeFactory = $typeFactory;

        $this->doctrineTypeToScalarType = [
            'tinyint' => new BooleanType(),
            // integers
            'smallint' => new IntegerType(),
            'mediumint' => new IntegerType(),
            'int' => new IntegerType(),
            'integer' => new IntegerType(),
            'bigint' => new IntegerType(),
            'numeric' => new IntegerType(),
            // floats
            'decimal' => new FloatType(),
            'float' => new FloatType(),
            'double' => new FloatType(),
            'real' => new FloatType(),
            // strings
            'tinytext' => new StringType(),
            'mediumtext' => new StringType(),
            'longtext' => new StringType(),
            'text' => new StringType(),
            'varchar' => new StringType(),
            'string' => new StringType(),
            'char' => new StringType(),
            'longblob' => new StringType(),
            'blob' => new StringType(),
            'mediumblob' => new StringType(),
            'tinyblob' => new StringType(),
            'binary' => new StringType(),
            'varbinary' => new StringType(),
            'set' => new StringType(),
            // date time objects
            'date' => new ObjectType(DateTimeInterface::class),
            'datetime' => new ObjectType(DateTimeInterface::class),
            'timestamp' => new ObjectType(DateTimeInterface::class),
            'time' => new ObjectType(DateTimeInterface::class),
            'year' => new ObjectType(DateTimeInterface::class),
        ];
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function inferProperty(Property $property): Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $doctrineColumnTagValueNode = $phpDocInfo->getByType(ColumnTagValueNode::class);
        if (! $doctrineColumnTagValueNode instanceof ColumnTagValueNode) {
            return new MixedType();
        }

        $type = $doctrineColumnTagValueNode->getType();
        if ($type === null) {
            return new MixedType();
        }

        $scalarType = $this->doctrineTypeToScalarType[$type] ?? null;
        if (! $scalarType instanceof Type) {
            return new MixedType();
        }

        $types = [$scalarType];

        // is nullable?
        if ($doctrineColumnTagValueNode->isNullable()) {
            $types[] = new NullType();
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    public function getPriority(): int
    {
        return 2000;
    }
}
