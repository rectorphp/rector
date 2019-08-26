<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use DateTimeInterface;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;

final class DoctrineColumnPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var string[]
     * @see \Doctrine\DBAL\Platforms\MySqlPlatform::initializeDoctrineTypeMappings()
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html#doctrine-mapping-types
     */
    private $doctrineTypeToScalarType = [
        'tinyint' => 'bool',
        'smallint' => 'int',
        'mediumint' => 'int',
        'int' => 'int',
        'integer' => 'int',
        'bigint' => 'int',
        'decimal' => 'float',
        'numeric' => 'int',
        'float' => 'float',
        'double' => 'float',
        'real' => 'float',
        'tinytext' => 'string',
        'mediumtext' => 'string',
        'longtext' => 'string',
        'text' => 'string',
        'varchar' => 'string',
        'string' => 'string',
        'char' => 'string',
        'longblob' => 'string',
        'blob' => 'string',
        'mediumblob' => 'string',
        'tinyblob' => 'string',
        'binary' => 'string',
        'varbinary' => 'string',
        'set' => 'string',

        'date' => DateTimeInterface::class,
        'datetime' => DateTimeInterface::class,
        'timestamp' => DateTimeInterface::class,
        'time' => DateTimeInterface::class,
        'year' => DateTimeInterface::class,
    ];

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    /**
     * @return string[]
     */
    public function inferProperty(Property $property): array
    {
        if ($property->getDocComment() === null) {
            return [];
        }

        $phpDocInfo = $this->docBlockManipulator->createPhpDocInfoFromNode($property);
        $doctrineColumnTagValueNode = $phpDocInfo->getDoctrineColumnTagValueNode();
        if ($doctrineColumnTagValueNode === null) {
            return [];
        }

        $scalarType = $this->doctrineTypeToScalarType[$doctrineColumnTagValueNode->getType()] ?? null;
        if ($scalarType === null) {
            return [];
        }

        $types = [$scalarType];

        // is nullable?
        if ($doctrineColumnTagValueNode->isNullable()) {
            $types[] = 'null';
        }

        return $types;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
