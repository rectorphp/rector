<?php

declare (strict_types=1);
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
final class DoctrineColumnPropertyTypeInferer implements \Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface
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
    private $doctrineTypeToScalarType = [];
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->typeFactory = $typeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->doctrineTypeToScalarType = [
            'tinyint' => new \PHPStan\Type\BooleanType(),
            // integers
            'smallint' => new \PHPStan\Type\IntegerType(),
            'mediumint' => new \PHPStan\Type\IntegerType(),
            'int' => new \PHPStan\Type\IntegerType(),
            'integer' => new \PHPStan\Type\IntegerType(),
            'bigint' => new \PHPStan\Type\IntegerType(),
            'numeric' => new \PHPStan\Type\IntegerType(),
            // floats
            'decimal' => new \PHPStan\Type\FloatType(),
            'float' => new \PHPStan\Type\FloatType(),
            'double' => new \PHPStan\Type\FloatType(),
            'real' => new \PHPStan\Type\FloatType(),
            // strings
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
    }
    /**
     * @param \PhpParser\Node\Stmt\Property $property
     */
    public function inferProperty($property) : ?\PHPStan\Type\Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $doctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClass('Doctrine\\ORM\\Mapping\\Column');
        if (!$doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return null;
        }
        $type = $doctrineAnnotationTagValueNode->getValueWithoutQuotes('type');
        if ($type === null) {
            return new \PHPStan\Type\MixedType();
        }
        $scalarType = $this->doctrineTypeToScalarType[$type] ?? null;
        if (!$scalarType instanceof \PHPStan\Type\Type) {
            return new \PHPStan\Type\MixedType();
        }
        $types = [$scalarType];
        $isNullable = $doctrineAnnotationTagValueNode->getValue('nullable');
        // is nullable?
        if ($isNullable instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode) {
            $types[] = new \PHPStan\Type\NullType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
    public function getPriority() : int
    {
        return 2000;
    }
}
