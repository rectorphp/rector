<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
final class ColumnPropertyTypeResolver
{
    /**
     * @var string
     */
    private const DATE_TIME_INTERFACE = 'DateTimeInterface';
    /**
     * @var string
     */
    private const COLUMN_CLASS = 'Doctrine\\ORM\\Mapping\\Column';
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    /**
     * @var array<string, Type>
     * @readonly
     */
    private $doctrineTypeToScalarType;
    /**
     * @param array<string, Type> $doctrineTypeToScalarType
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html#doctrine-mapping-types
     */
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, TypeFactory $typeFactory, AttributeFinder $attributeFinder, array $doctrineTypeToScalarType = null)
    {
        $doctrineTypeToScalarType = $doctrineTypeToScalarType ?? [
            'tinyint' => new BooleanType(),
            'boolean' => new BooleanType(),
            // integers
            'smallint' => new IntegerType(),
            'mediumint' => new IntegerType(),
            'int' => new IntegerType(),
            'integer' => new IntegerType(),
            'numeric' => new IntegerType(),
            // floats
            'float' => new FloatType(),
            'double' => new FloatType(),
            'real' => new FloatType(),
            // strings
            'decimal' => new StringType(),
            'bigint' => new StringType(),
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
            'date' => new ObjectType(self::DATE_TIME_INTERFACE),
            'datetime' => new ObjectType(self::DATE_TIME_INTERFACE),
            'timestamp' => new ObjectType(self::DATE_TIME_INTERFACE),
            'time' => new ObjectType(self::DATE_TIME_INTERFACE),
            'year' => new ObjectType(self::DATE_TIME_INTERFACE),
        ];
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeFactory = $typeFactory;
        $this->attributeFinder = $attributeFinder;
        $this->doctrineTypeToScalarType = $doctrineTypeToScalarType;
    }
    public function resolve(Property $property, bool $isNullable) : ?Type
    {
        $expr = $this->attributeFinder->findAttributeByClassArgByName($property, self::COLUMN_CLASS, 'type');
        if ($expr instanceof String_) {
            return $this->createPHPStanTypeFromDoctrineStringType($expr->value, $isNullable);
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        return $this->resolveFromPhpDocInfo($phpDocInfo, $isNullable);
    }
    /**
     * @return null|\PHPStan\Type\Type
     */
    private function resolveFromPhpDocInfo(PhpDocInfo $phpDocInfo, bool $isNullable)
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClass(self::COLUMN_CLASS);
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        $type = $doctrineAnnotationTagValueNode->getValueWithoutQuotes('type');
        if (!\is_string($type)) {
            return new MixedType();
        }
        return $this->createPHPStanTypeFromDoctrineStringType($type, $isNullable);
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\Type
     */
    private function createPHPStanTypeFromDoctrineStringType(string $type, bool $isNullable)
    {
        $scalarType = $this->doctrineTypeToScalarType[$type] ?? null;
        if (!$scalarType instanceof Type) {
            return new MixedType();
        }
        $types = [$scalarType];
        if ($isNullable) {
            $types[] = new NullType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
}
