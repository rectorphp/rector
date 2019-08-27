<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_;

use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

final class JoinColumnTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var bool
     */
    private $nullable = false;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string
     */
    private $referencedColumnName;

    /**
     * @var bool
     */
    private $unique = false;

    /**
     * @var string|null
     */
    private $onDelete;

    /**
     * @var string|null
     */
    private $columnDefinition;

    /**
     * @var string|null
     */
    private $fieldName;

    /**
     * @param string[] $orderedVisibleItems
     */
    public function __construct(
        ?string $name,
        string $referencedColumnName,
        bool $unique,
        bool $nullable,
        ?string $onDelete,
        ?string $columnDefinition,
        ?string $fieldName,
        array $orderedVisibleItems
    ) {
        $this->orderedVisibleItems = $orderedVisibleItems;
        $this->nullable = $nullable;
        $this->name = $name;
        $this->referencedColumnName = $referencedColumnName;
        $this->unique = $unique;
        $this->onDelete = $onDelete;
        $this->columnDefinition = $columnDefinition;
        $this->fieldName = $fieldName;
    }

    public function __toString(): string
    {
        $contentItems = [];

        $contentItems['nullable'] = sprintf('nullable=%s', $this->nullable ? 'true' : 'false');

        $contentItems['name'] = sprintf('name="%s"', $this->name);
        $contentItems['referencedColumnName'] = sprintf('referencedColumnName="%s"', $this->referencedColumnName);
        $contentItems['unique'] = sprintf('unique=%s', $this->unique ? 'true' : 'false');
        $contentItems['nullable'] = sprintf('nullable=%s', $this->nullable ? 'true' : 'false');
        $contentItems['onDelete'] = sprintf('onDelete="%s"', $this->onDelete);
        $contentItems['columnDefinition'] = sprintf('columnDefinition="%s"', $this->columnDefinition);
        $contentItems['fieldName'] = sprintf('fieldName="%s"', $this->fieldName);

        return $this->printContentItems($contentItems);
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }
}
