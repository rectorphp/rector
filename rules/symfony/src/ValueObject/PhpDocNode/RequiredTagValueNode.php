<?php

declare(strict_types=1);

namespace Rector\Symfony\ValueObject\PhpDocNode;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpdocParserPrinter\Attributes\AttributesTrait;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;

final class RequiredTagValueNode implements PhpDocTagValueNode, AttributeAwareInterface, PhpAttributableTagNodeInterface
{
    use AttributesTrait;

    public function __toString(): string
    {
        return '';
    }

    public function getShortName(): string
    {
        return 'Required';
    }

    public function getAttributeClassName(): string
    {
        return 'Symfony\Contracts\Service\Attribute\Required';
    }

    /**
     * @return mixed[]
     */
    public function getAttributableItems(): array
    {
        return [];
    }
}
