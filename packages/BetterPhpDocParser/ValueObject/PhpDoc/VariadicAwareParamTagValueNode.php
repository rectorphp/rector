<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Stringable;
final class VariadicAwareParamTagValueNode extends \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode
{
    public function __toString() : string
    {
        $variadic = $this->isVariadic ? '...' : '';
        $content = \sprintf('%s %s%s %s', $this->type, $variadic, $this->parameterName, $this->description);
        return \trim($content);
    }
}
