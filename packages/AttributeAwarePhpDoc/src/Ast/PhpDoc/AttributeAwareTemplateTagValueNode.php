<?php

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

final class AttributeAwareTemplateTagValueNode extends \PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode implements \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
{
    use \Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
}