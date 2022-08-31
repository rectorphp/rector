<?php

declare (strict_types=1);
namespace Rector\PhpDocParser;

use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
final class PhpDocInfoAnalyzer
{
    public function isVarDocAlreadySet(PhpDocInfo $phpDocInfo) : bool
    {
        foreach (['@var', '@phpstan-var', '@psalm-var'] as $tagName) {
            $varType = $phpDocInfo->getVarType($tagName);
            if (!$varType instanceof MixedType) {
                return \true;
            }
        }
        return \false;
    }
}
