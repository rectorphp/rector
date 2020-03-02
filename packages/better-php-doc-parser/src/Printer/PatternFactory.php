<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode;

final class PatternFactory
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
            /** @var AttributeAwareParamTagValueNode $paramTagValueNode */
            $paramTagValueNode = $phpDocTagNode->value;

            // type could be changed, so better keep it here
            $spacePattern .= self::TYPE_PATTERN;

            if ($paramTagValueNode->parameterName !== '') {
                $spacePattern .= '\s+';
                if ($paramTagValueNode->isReference()) {
                    $spacePattern .= '&';
                }

                if ($paramTagValueNode->isVariadic) {
                    $spacePattern .= '...';
                }

                $spacePattern .= preg_quote($paramTagValueNode->parameterName);
            }
        }

        return '#' . $spacePattern . '#';
    }
}
