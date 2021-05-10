<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Attributes;

use PHPStan\PhpDocParser\Ast\Node;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
final class AttributeMirrorer
{
    /**
     * @var string[]
     */
    private const ATTRIBUTES_TO_MIRROR = [\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::PARENT, \Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::START_AND_END, \Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::ORIG_NODE];
    public function mirror(\PHPStan\PhpDocParser\Ast\Node $oldNode, \PHPStan\PhpDocParser\Ast\Node $newNode) : void
    {
        foreach (self::ATTRIBUTES_TO_MIRROR as $attributeToMirror) {
            if (!$oldNode->hasAttribute($attributeToMirror)) {
                continue;
            }
            $attributeValue = $oldNode->getAttribute($attributeToMirror);
            $newNode->setAttribute($attributeToMirror, $attributeValue);
        }
    }
}
