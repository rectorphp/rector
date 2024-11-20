<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDoc;

use PhpParser\Node\Scalar\String_;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Stringable;
final class StringNode implements PhpDocTagValueNode
{
    public string $value;
    use NodeAttributes;
    public function __construct(string $value)
    {
        $this->value = $value;
        $this->value = \str_replace('""', '"', $this->value);
        if (\strpos($this->value, "'") !== \false && \strpos($this->value, "\n") === \false) {
            $kind = String_::KIND_DOUBLE_QUOTED;
        } else {
            $kind = String_::KIND_SINGLE_QUOTED;
        }
        $this->setAttribute(AttributeKey::KIND, $kind);
    }
    public function __toString() : string
    {
        return '"' . \str_replace('"', '""', $this->value) . '"';
    }
}
