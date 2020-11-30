<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

final class JoinColumnTagValueNode extends AbstractDoctrineTagValueNode implements TagAwareNodeInterface, PhpAttributableTagNodeInterface
{
    /**
     * @var string
     */
    private $shortName = '@ORM\JoinColumn';

    /**
     * @var string|null
     */
    private $tag;

    public function __construct(array $items, ?string $content = null, ?string $originalTag = null)
    {
        $this->tag = $originalTag;

        parent::__construct($items, $content);
    }

    public function isNullable(): ?bool
    {
        return $this->items['nullable'];
    }

    public function getTag(): string
    {
        return $this->tag ?: $this->shortName;
    }

    public function getUnique(): ?bool
    {
        return $this->items['unique'];
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function changeShortName(string $shortName): void
    {
        $this->shortName = $shortName;
    }

    public function getAttributeClassName(): string
    {
        return 'TBA';
    }
}
