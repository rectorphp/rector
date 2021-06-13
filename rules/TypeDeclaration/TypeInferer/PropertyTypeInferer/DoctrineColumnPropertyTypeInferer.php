<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;

final class DoctrineColumnPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var string
     */
    private const DATE_TIME_INTERFACE = 'DateTimeInterface';

    /**
     * @var Type[]
     *
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html#doctrine-mapping-types
     */
    private array $doctrineTypeToScalarType = [];

    public function __construct(
        private TypeFactory $typeFactory,
        private PhpDocInfoFactory $phpDocInfoFactory
    ) {
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
            'date' => new ObjectType(self::DATE_TIME_INTERFACE),
            'datetime' => new ObjectType(self::DATE_TIME_INTERFACE),
            'timestamp' => new ObjectType(self::DATE_TIME_INTERFACE),
            'time' => new ObjectType(self::DATE_TIME_INTERFACE),
            'year' => new ObjectType(self::DATE_TIME_INTERFACE),
        ];
    }

    public function inferProperty(Property $property): ?Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Doctrine\ORM\Mapping\Column');
        if (! $doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }

        $type = $doctrineAnnotationTagValueNode->getValueWithoutQuotes('type');
        if ($type === null) {
            return new MixedType();
        }

        $scalarType = $this->doctrineTypeToScalarType[$type] ?? null;
        if (! $scalarType instanceof Type) {
            return new MixedType();
        }

        $types = [$scalarType];

        $isNullable = $doctrineAnnotationTagValueNode->getValue('nullable');

        // is nullable?
        if ($isNullable instanceof ConstExprTrueNode) {
            $types[] = new NullType();
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    public function getPriority(): int
    {
        return 2000;
    }
}
