<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Contract\PhpDocParser;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
interface PhpDocNodeDecoratorInterface
{
    /**
     * @api implemented by child classes
     */
    public function decorate(PhpDocNode $phpDocNode, Node $phpNode): void;
}
