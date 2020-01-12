<?php

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

final class AttributeAwareMethodTagValueParameterNode extends \PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode implements \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
{
    use \Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
}