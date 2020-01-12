<?php

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

final class AttributeAwareUsesTagValueNode extends \PHPStan\PhpDocParser\Ast\PhpDoc\UsesTagValueNode implements \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
{
    use \Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
}