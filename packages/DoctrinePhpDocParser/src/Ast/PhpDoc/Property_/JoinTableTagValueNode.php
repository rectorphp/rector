<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_;

use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

final class JoinTableTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@ORM\JoinTable';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $schema;

    /**
     * @var JoinColumnTagValueNode[]|null
     */
    private $joinColumns;

    /**
     * @var JoinColumnTagValueNode[]|null
     */
    private $inverseJoinColumns;

    /**
     * @param JoinColumnTagValueNode[] $joinColumns
     * @param JoinColumnTagValueNode[] $inverseJoinColumns
     */
    public function __construct(
        string $name,
        ?string $schema = null,
        ?array $joinColumns = null,
        ?array $inverseJoinColumns = null,
        ?string $originalContent = null
    ) {
        $this->name = $name;
        $this->schema = $schema;
        $this->joinColumns = $joinColumns;
        $this->inverseJoinColumns = $inverseJoinColumns;
        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        $contentItems = [];

        $contentItems['name'] = sprintf('name="%s"', $this->name);

        if ($this->schema !== null) {
            $contentItems['schema'] = sprintf('schema="%s"', $this->schema);
        }

        if ($this->joinColumns) {
            $contentItems['joinColumns'] = $this->printNestedTag(
                $this->joinColumns,
                JoinColumnTagValueNode::SHORT_NAME,
                'joinColumns'
            );
        }

        if ($this->inverseJoinColumns) {
            $contentItems['inverseJoinColumns'] = $this->printNestedTag(
                $this->inverseJoinColumns,
                JoinColumnTagValueNode::SHORT_NAME,
                'inverseJoinColumns'
            );
        }

        return $this->printContentItems($contentItems);
    }
}
