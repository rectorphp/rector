<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_;

use Rector\DoctrinePhpDocParser\Array_\ArrayItemStaticHelper;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

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
     * @var mixed[]|null
     */
    private $indexes;

    /**
     * @var mixed[]|null
     */
    private $uniqueConstaints;

    /**
     * @var mixed[]
     */
    private $options = [];

    /**
     * @param mixed[] $options
     */
    public function __construct(
        ?string $name,
        ?string $schema,
        ?array $indexes,
        ?array $uniqueConstaints,
        array $options,
        ?string $originalContent = null
    ) {
        $this->name = $name;
        $this->schema = $schema;
        $this->indexes = $indexes;
        $this->uniqueConstaints = $uniqueConstaints;
        $this->options = $options;

        if ($originalContent !== null) {
            $this->orderedVisibleItems = ArrayItemStaticHelper::resolveAnnotationItemsOrder($originalContent);
        }
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

        if ($this->indexes) {
            $contentItems['indexes'] = $this->printArrayItem($this->indexes, 'indexes');
        }

        if ($this->uniqueConstaints) {
            $contentItems['uniqueConstaints'] = $this->printArrayItem($this->uniqueConstaints, 'uniqueConstaints');
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
