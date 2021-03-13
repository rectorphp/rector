<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\GenericTagValueNode;

final class SpacePatternFactory
{
    /**
     * @var string
     */
    private const TYPE_PATTERN = '[\w\\\\\[\]\(\)\{\}\:\?\$\-\,\&|<>\s]+';

    public function createSpacePattern(PhpDocTagNode $phpDocTagNode): string
    {
        $spacePattern = preg_quote($phpDocTagNode->name, '#') . '(?<space>\s+)';

        // we have to match exact @param space, in case of multiple @param s
        if ($phpDocTagNode->value instanceof AttributeAwareParamTagValueNode) {
            return $this->createSpacePatternForParamTagValueNode($phpDocTagNode->value, $spacePattern);
        }

        if ($phpDocTagNode->value instanceof GenericTagValueNode) {
            $originalValue = $phpDocTagNode->value->getAttribute('original_value') ?? $phpDocTagNode->value->value;

            // break by line break, to prevent false content positive
            $originalValueParts = explode(PHP_EOL, $originalValue);
            if (isset($originalValueParts[0])) {
                $originalValue = $originalValueParts[0];
            }

            $spacePattern .= preg_quote($originalValue, '#');
        }

        return '#' . $spacePattern . '#';
    }

    private function createSpacePatternForParamTagValueNode(
        ParamTagValueNode $paramTagValueNode,
        string $spacePattern
    ): string {
        // type could be changed, so better keep it here
        $spacePattern .= self::TYPE_PATTERN;

        if ($paramTagValueNode->parameterName !== '') {
            $spacePattern .= '\s+';

            if ($paramTagValueNode->isVariadic) {
                $spacePattern .= '...';
            }

            $spacePattern .= preg_quote($paramTagValueNode->parameterName, '#');
        }

        return '#' . $spacePattern . '#';
    }
}
