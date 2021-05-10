<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Printer;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
final class EmptyPhpDocDetector
{
    public function isPhpDocNodeEmpty(PhpDocNode $phpDocNode) : bool
    {
        if ($phpDocNode->children === []) {
            return \true;
        }
        foreach ($phpDocNode->children as $phpDocChildNode) {
            if ($phpDocChildNode instanceof PhpDocTextNode) {
                if ($phpDocChildNode->text !== '') {
                    return \false;
                }
            } else {
                return \false;
            }
        }
        return \true;
    }
}
