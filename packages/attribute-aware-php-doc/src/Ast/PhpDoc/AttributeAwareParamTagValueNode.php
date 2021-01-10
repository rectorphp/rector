<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\PhpdocParserPrinter\Attributes\AttributesTrait;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;

final class AttributeAwareParamTagValueNode extends ParamTagValueNode implements AttributeAwareInterface, TypeAwareTagValueNodeInterface
{
    use AttributesTrait;

    public function __toString(): string
    {
        $variadic = $this->isVariadic ? '...' : '';

        $content = sprintf('%s %s%s %s', $this->type, $variadic, $this->parameterName, $this->description);

        return trim($content);
    }
}
