<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class JoinColumnTagValueNode extends AbstractDoctrineTagValueNode implements TagAwareNodeInterface
{
    /**
     * @var string
     */
    private $shortName = '@ORM\JoinColumn';

    /**
     * @var string|null
     */
    private $tag;

    public function __construct(
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter,
        array $items,
        ?string $content = null,
        ?string $originalTag = null
    ) {
        $this->tag = $originalTag;

        parent::__construct(
            $arrayPartPhpDocTagPrinter,
            $tagValueNodePrinter,
            $items,
            $content
        );
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
}
