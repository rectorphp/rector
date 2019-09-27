<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class TableTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@ORM\Table';

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $schema;

    /**
     * @var IndexTagValueNode[]
     */
    private $indexes = [];

    /**
     * @var UniqueConstraintTagValueNode[]
     */
    private $uniqueConstraints = [];

    /**
     * @var mixed[]
     */
    private $options = [];

    /**
     * @var bool
     */
    private $haveIndexesFinalComma = false;

    /**
     * @var bool
     */
    private $haveUniqueConstraintsFinalComma = false;

    /**
     * @param mixed[] $options
     * @param IndexTagValueNode[] $indexes
     * @param UniqueConstraintTagValueNode[] $uniqueConstraints
     */
    public function __construct(
        ?string $name,
        ?string $schema,
        array $indexes,
        array $uniqueConstraints,
        array $options,
        ?string $originalContent = null,
        bool $haveIndexesFinalComma = false,
        bool $haveUniqueConstraintsFinalComma = false
    ) {
        $this->name = $name;
        $this->schema = $schema;
        $this->indexes = $indexes;
        $this->uniqueConstraints = $uniqueConstraints;
        $this->options = $options;

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent);
        }

        $this->haveIndexesFinalComma = $haveIndexesFinalComma;
        $this->haveUniqueConstraintsFinalComma = $haveUniqueConstraintsFinalComma;
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->name !== null) {
            $contentItems['name'] = sprintf('name="%s"', $this->name);
        }

        if ($this->schema !== null) {
            $contentItems['schema'] = sprintf('schema="%s"', $this->schema);
        }

        if ($this->indexes !== []) {
            $contentItems['indexes'] = $this->printNestedTag($this->indexes, 'indexes', $this->haveIndexesFinalComma);
        }

        if ($this->uniqueConstraints !== []) {
            $contentItems['uniqueConstraints'] = $this->printNestedTag(
                $this->uniqueConstraints,
                'uniqueConstraints',
                $this->haveUniqueConstraintsFinalComma
            );
        }

        if ($this->options !== []) {
            $contentItems['options'] = $this->printArrayItem($this->options, 'options');
        }

        return $this->printContentItems($contentItems);
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
