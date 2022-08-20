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
     * @var mixed
     */
    public $value;
    /**
     * @var mixed
     */
    public $key;
    /**
     * @var String_::KIND_*|null
     */
    public $kindValueQuoted = null;
    /**
     * @var int|null
     */
    public $kindKeyQuoted = null;
    /**
     * @param String_::KIND_*|null $kindValueQuoted
     * @param mixed $value
     * @param mixed $key
     */
    public function __construct($value, $key, ?int $kindValueQuoted = null, ?int $kindKeyQuoted = null)
    {
        $this->value = $value;
        $this->key = $key;
        $this->kindValueQuoted = $kindValueQuoted;
        $this->kindKeyQuoted = $kindKeyQuoted;
    }
    public function __toString() : string
    {
        $value = '';
        if ($this->kindKeyQuoted === String_::KIND_DOUBLE_QUOTED) {
            $value .= '"' . $this->key . '" = ';
        } elseif ($this->key !== null) {
            $value .= $this->key . '=';
        }
        // @todo depends on the context! possibly the top array is quting this stinrg already
        if ($this->kindValueQuoted === String_::KIND_DOUBLE_QUOTED) {
            $value .= '"' . $this->value . '"';
        } elseif (\is_array($this->value)) {
            foreach ($this->value as $singleValue) {
                $value .= $singleValue;
            }
        } else {
            $value .= $this->value;
        }
        return $value;
    }
}
