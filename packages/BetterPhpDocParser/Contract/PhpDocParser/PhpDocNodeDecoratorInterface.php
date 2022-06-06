<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\Contract\PhpDocParser;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
interface PhpDocNodeDecoratorInterface
{
    public function decorate(PhpDocNode $phpDocNode, Node $phpNode) : void;
}
