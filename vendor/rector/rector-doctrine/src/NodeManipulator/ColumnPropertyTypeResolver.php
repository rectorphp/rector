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
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\Doctrine\NodeAnalyzer\AttributeFinder $attributeFinder, array $doctrineTypeToScalarType = null)
    {
        $doctrineTypeToScalarType = $doctrineTypeToScalarType ?? [
            'tinyint' => new \PHPStan\Type\BooleanType(),
            // integers
            'smallint' => new \PHPStan\Type\IntegerType(),
            'mediumint' => new \PHPStan\Type\IntegerType(),
            'int' => new \PHPStan\Type\IntegerType(),
            'integer' => new \PHPStan\Type\IntegerType(),
            'numeric' => new \PHPStan\Type\IntegerType(),
            // floats
            'float' => new \PHPStan\Type\FloatType(),
            'double' => new \PHPStan\Type\FloatType(),
            'real' => new \PHPStan\Type\FloatType(),
            // strings
            'decimal' => new \PHPStan\Type\StringType(),
            'bigint' => new \PHPStan\Type\StringType(),
            'tinytext' => new \PHPStan\Type\StringType(),
            'mediumtext' => new \PHPStan\Type\StringType(),
            'longtext' => new \PHPStan\Type\StringType(),
            'text' => new \PHPStan\Type\StringType(),
            'varchar' => new \PHPStan\Type\StringType(),
            'string' => new \PHPStan\Type\StringType(),
            'char' => new \PHPStan\Type\StringType(),
            'longblob' => new \PHPStan\Type\StringType(),
            'blob' => new \PHPStan\Type\StringType(),
            'mediumblob' => new \PHPStan\Type\StringType(),
            'tinyblob' => new \PHPStan\Type\StringType(),
            'binary' => new \PHPStan\Type\StringType(),
            'varbinary' => new \PHPStan\Type\StringType(),
            'set' => new \PHPStan\Type\StringType(),
            // date time objects
            'date' => new \PHPStan\Type\ObjectType(self::DATE_TIME_INTERFACE),
            'datetime' => new \PHPStan\Type\ObjectType(self::DATE_TIME_INTERFACE),
            'timestamp' => new \PHPStan\Type\ObjectType(self::DATE_TIME_INTERFACE),
            'time' => new \PHPStan\Type\ObjectType(self::DATE_TIME_INTERFACE),
            'year' => new \PHPStan\Type\ObjectType(self::DATE_TIME_INTERFACE),
        ];
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeFactory = $typeFactory;
        $this->attributeFinder = $attributeFinder;
        $this->doctrineTypeToScalarType = $doctrineTypeToScalarType;
    }
    public function resolve(\PhpParser\Node\Stmt\Property $property, bool $isNullable) : ?\PHPStan\Type\Type
    {
        $argValue = $this->attributeFinder->findAttributeByClassArgByName($property, self::COLUMN_CLASS, 'type');
        if ($argValue instanceof \PhpParser\Node\Scalar\String_) {
            return $this->createPHPStanTypeFromDoctrineStringType($argValue->value, $isNullable);
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        return $this->resolveFromPhpDocInfo($phpDocInfo, $isNullable);
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    private function resolveFromPhpDocInfo(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, bool $isNullable)
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClass(self::COLUMN_CLASS);
        if (!$doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return null;
        }
        $type = $doctrineAnnotationTagValueNode->getValueWithoutQuotes('type');
        if (!\is_string($type)) {
            return new \PHPStan\Type\MixedType();
        }
        return $this->createPHPStanTypeFromDoctrineStringType($type, $isNullable);
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\Type
     */
    private function createPHPStanTypeFromDoctrineStringType(string $type, bool $isNullable)
    {
        $scalarType = $this->doctrineTypeToScalarType[$type] ?? null;
        if (!$scalarType instanceof \PHPStan\Type\Type) {
            return new \PHPStan\Type\MixedType();
        }
        $types = [$scalarType];
        if ($isNullable) {
            $types[] = new \PHPStan\Type\NullType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
}
