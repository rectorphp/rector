<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;

final class AttributeAwareParamTagValueNode extends ParamTagValueNode implements AttributeAwareNodeInterface, TypeAwareTagValueNodeInterface
{
    use AttributeTrait;

    public function __toString(): string
    {
        $variadic = $this->isVariadic ? '...' : '';

        $content = sprintf('%s %s%s %s', $this->type, $variadic, $this->parameterName, $this->description);

        return trim($content);
    }
}
