<?php

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

final class AttributeAwareReturnTagValueNode extends \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode implements \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
{
    use \Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
}