<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AbstractConstraintTagValueNode;
use Symfony\Component\Validator\Constraints\Range;

final class AssertRangeTagValueNode extends AbstractConstraintTagValueNode implements TypeAwareTagValueNodeInterface, ShortNameAwareTagInterface, PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    /**
     * @var mixed[]
     */
    private $items = [];

    public function __construct(Range $range, string $originalContent)
    {
        $this->items = get_object_vars($range);

        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        $contentItemsWithExplicitKeys = $this->makeKeysExplicit($this->items);

        return $this->printContentItems($contentItemsWithExplicitKeys);
    }

    public function getShortName(): string
    {
        return '@Assert\Range';
    }

    public function toAttributeString(): string
    {
        $items = $this->filterOutMissingItems($this->items);
        $items = $this->completeItemsQuotes($items);

        $content = $this->printPhpAttributeItemsAsArray($items);

        return $this->printAttributeContent($content);
    }
}
