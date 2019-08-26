<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_;

use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

final class JoinTableTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $schema;

    /**
     * @var JoinColumnTagValueNode[]
     */
    private $joinColumns = [];

    /**
     * @var array|null
     */
    private $inverseJoinColumns;

    /**
     * @param string[] $orderedVisibleItems
     * @param JoinColumnTagValueNode[] $joinColumns
     */
    public function __construct(
        string $name,
        ?string $schema,
        array $joinColumns,
        ?array $inverseJoinColumns,
        array $orderedVisibleItems
    ) {
        $this->name = $name;
        $this->schema = $schema;
        $this->joinColumns = $joinColumns;
        $this->inverseJoinColumns = $inverseJoinColumns;
        $this->orderedVisibleItems = $orderedVisibleItems;
    }

    public function __toString(): string
    {
        $contentItems = [];

        $contentItems['name'] = sprintf('name="%s"', $this->name);
        $contentItems['schema'] = sprintf('schema="%s"', $this->schema);

        if ($this->joinColumns) {
            dump($this->joinColumns);
            die;

            $contentItems['joinColumns'] = sprintf('joinColumns=%s', $this->joinColumns);
        }

        if ($this->inverseJoinColumns) {
            $contentItems['inverseJoinColumns'] = sprintf('inverseJoinColumns=%s', $this->inverseJoinColumns);
        }

        return $this->printContentItems($contentItems);
    }
}
