<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class CustomIdGeneratorTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    private $class;

    public function __construct(string $class, ?string $originalContent = null)
    {
        $this->class = $class;
        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        return '(' . $this->printValueWithOptionalQuotes('class', $this->class) . ')';
    }

    public function getShortName(): string
    {
        return '@ORM\CustomIdGenerator';
    }
}
