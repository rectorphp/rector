<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

final class EntityTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    /**
     * @var string|null
     */
    private $repositoryClass;

    /**
     * @var bool|null
     */
    private $readOnly;

    public function __construct(
        ?string $repositoryClass = null,
        ?bool $readOnly = null,
        ?string $originalContent = null
    ) {
        $this->repositoryClass = $repositoryClass;
        $this->readOnly = $readOnly;

        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        $items = $this->createItems();
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public function removeRepositoryClass(): void
    {
        $this->repositoryClass = null;
    }

    public function getShortName(): string
    {
        return '@ORM\Entity';
    }

    public function toAttributeString(): string
    {
        $items = $this->createItems(self::PRINT_TYPE_ATTRIBUTE);
        $items = $this->filterOutMissingItems($items);

        $content = $this->printPhpAttributeItems($items);
        return $this->printAttributeContent($content);
    }

    private function createItems(string $printType = self::PRINT_TYPE_ANNOTATION): array
    {
        $items = [];

        if ($this->repositoryClass !== null) {
            if ($printType === self::PRINT_TYPE_ATTRIBUTE) {
                $items['repositoryClass'] = $this->repositoryClass . '::class';
            } else {
                $items['repositoryClass'] = $this->printValueWithOptionalQuotes(null, $this->repositoryClass);
            }
        }

        if ($this->readOnly !== null) {
            if ($printType === self::PRINT_TYPE_ATTRIBUTE) {
                $items['readOnly'] = $this->readOnly ? 'ORM\Entity::READ_ONLY' : '';
            } else {
                $items['readOnly'] = $this->readOnly ? 'true' : 'false';
            }
        }

        return $items;
    }
}
