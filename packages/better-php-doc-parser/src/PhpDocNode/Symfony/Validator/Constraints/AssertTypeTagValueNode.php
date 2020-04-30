<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNodeFactory\Symfony\Validator\Constraints\AssertTypePhpDocNodeFactory
 *
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\AssertTypeTagValueNodeTest
 */
final class AssertTypeTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var mixed[]
     */
    private $items = [];

    public function __construct(Type $type, ?string $originalContent = null)
    {
        $this->items = get_object_vars($type);

        $this->resolveOriginalContentSpacingAndOrder($originalContent, 'type');
    }

    public function __toString(): string
    {
        $items = $this->completeItemsQuotes($this->items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public function getShortName(): string
    {
        return '@Assert\Type';
    }
}
