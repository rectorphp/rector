<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class MultilineSpaceFormatPreserver
{
    public function resolveCurrentPhpDocNodeText(AttributeAwareNodeInterface $attributeAwareNode): ?string
    {
        if ($attributeAwareNode instanceof PhpDocTagNode) {
            if (property_exists($attributeAwareNode->value, 'description')) {
                return $attributeAwareNode->value->description;
            }
        }

        if ($attributeAwareNode instanceof PhpDocTextNode) {
            return $attributeAwareNode->text;
        }

        if (! $attributeAwareNode instanceof AttributeAwarePhpDocTagNode) {
            return null;
        }

        if (! $attributeAwareNode->value instanceof AttributeAwareGenericTagValueNode) {
            return null;
        }

        if (substr_count($attributeAwareNode->value->value, "\n") > 0) {
            return $attributeAwareNode->value->value;
        }

        return null;
    }

    public function setNewTextToPhpDocNode(
        AttributeAwareNodeInterface $attributeAwareNode,
        string $newText
    ): AttributeAwareNodeInterface {
        if ($attributeAwareNode instanceof PhpDocTagNode) {
            if (property_exists($attributeAwareNode->value, 'description')) {
                $attributeAwareNode->value->description = $newText;
            }
        }

        if ($attributeAwareNode instanceof PhpDocTextNode) {
            $attributeAwareNode->text = $newText;
        }

        if ($attributeAwareNode instanceof AttributeAwarePhpDocTagNode) {
            if ($attributeAwareNode->value instanceof AttributeAwareGenericTagValueNode) {
                $attributeAwareNode->value->value = $newText;
            }
        }

        return $attributeAwareNode;
    }

    /**
     * Fix multiline BC break - https://github.com/phpstan/phpdoc-parser/pull/26/files
     */
    public function fixMultilineDescriptions(
        AttributeAwareNodeInterface $attributeAwareNode
    ): AttributeAwareNodeInterface {
        if (! $attributeAwareNode->getAttribute(Attribute::ORIGINAL_CONTENT)) {
            return $attributeAwareNode;
        }

        $nodeWithRestoredSpaces = $this->restoreOriginalSpacingInText($attributeAwareNode);
        if ($nodeWithRestoredSpaces !== null) {
            $attributeAwareNode = $nodeWithRestoredSpaces;
            $attributeAwareNode->setAttribute(Attribute::HAS_DESCRIPTION_WITH_ORIGINAL_SPACES, true);
        }

        return $attributeAwareNode;
    }

    /**
     * @param PhpDocTextNode|AttributeAwareNodeInterface $attributeAwareNode
     */
    private function restoreOriginalSpacingInText(
        AttributeAwareNodeInterface $attributeAwareNode
    ): ?AttributeAwareNodeInterface {
        /** @var string $originalContent */
        $originalContent = $attributeAwareNode->getAttribute(Attribute::ORIGINAL_CONTENT);
        $oldSpaces = Strings::matchAll($originalContent, '#\s+#ms');

        $currentText = $this->resolveCurrentPhpDocNodeText($attributeAwareNode);
        if ($currentText === null) {
            return null;
        }

        $newParts = Strings::split($currentText, '#\s+#');

        // we can't do this!
        if (count($oldSpaces) + 1 !== count($newParts)) {
            return null;
        }

        $newText = '';
        foreach ($newParts as $key => $newPart) {
            $newText .= $newPart;
            if (isset($oldSpaces[$key])) {
                if (Strings::match($oldSpaces[$key][0], '#\n {1,}$#s')) {
                    // remove last extra space
                    $oldSpaces[$key][0] = Strings::substring($oldSpaces[$key][0], 0, -1);
                }

                $newText .= $oldSpaces[$key][0];
            }
        }

        if ($newText === '') {
            return null;
        }

        return $this->setNewTextToPhpDocNode($attributeAwareNode, $newText);
    }
}
