<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\BaseNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;

final class MultilineSpaceFormatPreserver
{
    /**
     * @var string
     * @see https://regex101.com/r/R2zdQt/1
     */
    public const NEWLINE_WITH_SPACE_REGEX = '#\n {1,}$#s';

    public function resolveCurrentPhpDocNodeText(Node $node): ?string
    {
        if ($node instanceof PhpDocTagNode && property_exists($node->value, 'description')) {
            return $node->value->description;
        }

        if ($node instanceof PhpDocTextNode) {
            return $node->text;
        }

        if (! $node instanceof PhpDocTagNode) {
            return null;
        }

        if (! $node->value instanceof GenericTagValueNode) {
            return null;
        }

        if (substr_count($node->value->value, "\n") > 0) {
            return $node->value->value;
        }

        return null;
    }

    /**
     * Fix multiline BC break - https://github.com/phpstan/phpdoc-parser/pull/26/files
     * @param BaseNode $node
     */
    public function fixMultilineDescriptions(Node $node): void
    {
        $originalContent = $node->getAttribute(Attribute::ORIGINAL_CONTENT);
        if (! $originalContent) {
            return;
        }

        $nodeWithRestoredSpaces = $this->restoreOriginalSpacingInText($node);
        if (! $nodeWithRestoredSpaces instanceof Node) {
            return;
        }

        $node = $nodeWithRestoredSpaces;
        $node->setAttribute(Attribute::HAS_DESCRIPTION_WITH_ORIGINAL_SPACES, true);
    }

    /**
     * @param BaseNode $node
     */
    private function restoreOriginalSpacingInText(Node $node): ?Node
    {
        /** @var string $originalContent */
        $originalContent = $node->getAttribute(Attribute::ORIGINAL_CONTENT);
        $oldSpaces = Strings::matchAll($originalContent, '#\s+#ms');

        $currentText = $this->resolveCurrentPhpDocNodeText($node);
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
                if (Strings::match($oldSpaces[$key][0], self::NEWLINE_WITH_SPACE_REGEX)) {
                    // remove last extra space
                    $oldSpaces[$key][0] = Strings::substring($oldSpaces[$key][0], 0, -1);
                }

                $newText .= $oldSpaces[$key][0];
            }
        }

        if ($newText === '') {
            return null;
        }

        $this->decoratePhpDocNodeWithNewText($node, $newText);
        return $node;
    }

    private function decoratePhpDocNodeWithNewText(Node $node, string $newText): void
    {
        if ($node instanceof PhpDocTagNode && property_exists($node->value, 'description')) {
            $node->value->description = $newText;
        }

        if ($node instanceof PhpDocTextNode) {
            $node->text = $newText;
        }

        if (! $node instanceof PhpDocTagNode) {
            return;
        }

        if (! $node->value instanceof GenericTagValueNode) {
            return;
        }

        $node->value->value = $newText;
    }
}
