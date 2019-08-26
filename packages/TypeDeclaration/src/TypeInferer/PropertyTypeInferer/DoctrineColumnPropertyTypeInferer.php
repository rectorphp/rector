<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;

final class DoctrineColumnPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var string
     */
    private const COLUMN_ANNOTATION = Column::class;

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
        if (! $this->docBlockManipulator->hasTag($property, self::COLUMN_ANNOTATION)) {
            return [];
        }

        $columnTag = $this->docBlockManipulator->getTagByName($property, self::COLUMN_ANNOTATION);
        if (! $columnTag->value instanceof GenericTagValueNode) {
            return [];
        }

        $match = Strings::match($columnTag->value->value, '#type=\"(?<type>.*?)\"#');
        if (! isset($match['type'])) {
            return [];
        }

        $doctrineType = $match['type'];
        $scalarType = $this->doctrineTypeToScalarType[$doctrineType] ?? null;

        if ($scalarType === null) {
            return [];
        }

        $types = [$scalarType];

        // is nullable?
        if ($this->isNullable($columnTag->value->value)) {
            $types[] = 'null';
        }

        return $types;
    }

    public function getPriority(): int
    {
        return 1000;
    }

    private function isNullable(string $value): bool
    {
        return (bool) Strings::match($value, '#nullable=true#');
    }
}
