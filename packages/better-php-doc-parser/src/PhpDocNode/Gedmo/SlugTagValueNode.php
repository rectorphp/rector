<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\Slug;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class SlugTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var mixed[]
     */
    private $items = [];

    public function __construct(Slug $slug, ?string $originalContent = null)
    {
        $this->items = get_object_vars($slug);

        $this->resolveOriginalContentSpacingAndOrder($originalContent, 'fields');
    }

    public function __toString(): string
    {
        $items = $this->completeItemsQuotes($this->items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public function getFields(): array
    {
        return $this->items['fields'];
    }

    public function getShortName(): string
    {
        return '@Gedmo\Slug';
    }
}
