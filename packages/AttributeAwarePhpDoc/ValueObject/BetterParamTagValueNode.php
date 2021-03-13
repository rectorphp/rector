<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\ValueObject;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;

final class BetterParamTagValueNode extends ParamTagValueNode implements TypeAwareTagValueNodeInterface
{
    public function __toString(): string
    {
        $variadic = $this->isVariadic ? '...' : '';

        $content = sprintf('%s %s%s %s', $this->type, $variadic, $this->parameterName, $this->description);

        return trim($content);
    }
}
