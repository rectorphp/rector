<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;

final class VariadicAwareParamTagValueNode extends ParamTagValueNode
{
    public function __toString(): string
    {
        $variadic = $this->isVariadic ? '...' : '';

        $content = sprintf('%s %s%s %s', $this->type, $variadic, $this->parameterName, $this->description);

        return trim($content);
    }
}
