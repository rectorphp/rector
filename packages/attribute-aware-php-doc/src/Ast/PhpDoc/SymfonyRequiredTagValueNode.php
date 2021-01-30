<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

final class SymfonyRequiredTagValueNode implements PhpDocTagValueNode, AttributeAwareNodeInterface, PhpAttributableTagNodeInterface
{
    use AttributeTrait;

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
