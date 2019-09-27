<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class JoinColumnTagValueNode extends AbstractDoctrineTagValueNode implements TagAwareNodeInterface
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@ORM\JoinColumn';

    /**
     * @var bool|null
     */
    private $nullable;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $referencedColumnName;

    /**
     * @var bool|null
     */
    private $unique;

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
     * @var string|null
     */
    private $tag;

    public function __construct(
        ?string $name,
        string $referencedColumnName,
        ?bool $unique = null,
        ?bool $nullable = null,
        ?string $onDelete = null,
        ?string $columnDefinition = null,
        ?string $fieldName = null,
        ?string $originalContent = null,
        ?string $originalTag = null
    ) {
        $this->nullable = $nullable;
        $this->name = $name;
        $this->referencedColumnName = $referencedColumnName;
        $this->unique = $unique;
        $this->onDelete = $onDelete;
        $this->columnDefinition = $columnDefinition;
        $this->fieldName = $fieldName;

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent);
            $this->tag = $originalTag;
        }
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->nullable !== null) {
            $contentItems['nullable'] = sprintf('nullable=%s', $this->nullable ? 'true' : 'false');
        }

        if ($this->name) {
            $contentItems['name'] = sprintf('name="%s"', $this->name);
        }

        if ($this->referencedColumnName !== null) {
            $contentItems['referencedColumnName'] = sprintf('referencedColumnName="%s"', $this->referencedColumnName);
        }

        if ($this->unique !== null) {
            $contentItems['unique'] = sprintf('unique=%s', $this->unique ? 'true' : 'false');
        }

        if ($this->nullable !== null) {
            $contentItems['nullable'] = sprintf('nullable=%s', $this->nullable ? 'true' : 'false');
        }

        if ($this->onDelete !== null) {
            $contentItems['onDelete'] = sprintf('onDelete="%s"', $this->onDelete);
        }

        if ($this->columnDefinition !== null) {
            $contentItems['columnDefinition'] = sprintf('columnDefinition="%s"', $this->columnDefinition);
        }

        if ($this->fieldName !== null) {
            $contentItems['fieldName'] = sprintf('fieldName="%s"', $this->fieldName);
        }

        return $this->printContentItems($contentItems);
    }

    public function changeNullable(bool $nullable): void
    {
        $this->nullable = $nullable;
    }

    public function changeReferencedColumnName(string $referencedColumnName): void
    {
        $this->orderedVisibleItems[] = 'referencedColumnName';
        $this->referencedColumnName = $referencedColumnName;
    }

    public function isNullable(): ?bool
    {
        return $this->nullable;
    }

    public function changeName(string $newName): void
    {
        $this->name = $newName;
    }

    public function getTag(): ?string
    {
        return $this->tag ?: self::SHORT_NAME;
    }
}
