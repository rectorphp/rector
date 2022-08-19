<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDoc;

use PhpParser\Node\Scalar\String_;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Stringable;
final class ArrayItemNode implements PhpDocTagValueNode
{
    use NodeAttributes;
    /**
     * @readonly
     * @var mixed
     */
    private $value;
    /**
     * @var String_::KIND_*|null
     * @readonly
     */
    private $kindValueQuoted = null;
    /**
     * @param String_::KIND_*|null $kindValueQuoted
     * @param mixed $value
     */
    public function __construct($value, ?int $kindValueQuoted = null)
    {
        $this->value = $value;
        $this->kindValueQuoted = $kindValueQuoted;
    }
    public function __toString() : string
    {
        $value = '';
        // @todo depends on the context! possibly the top array is quting this stinrg already
        if ($this->kindValueQuoted === String_::KIND_DOUBLE_QUOTED) {
            $value .= '"' . $this->value . '"';
        }
        return $value;
    }
}
