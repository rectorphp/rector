<?php

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

final class AttributeAwareVarTagValueNode extends \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode implements \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
{
    use \Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
}