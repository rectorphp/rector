<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Stringable;
final class ArrayItemNode implements PhpDocTagValueNode
{
    /**
     * @var mixed
     */
    public $value;
    /**
     * @var mixed
     */
    public $key = null;
    use NodeAttributes;
    /**
     * @param mixed $value
     * @param mixed $key
     */
    public function __construct($value, $key = null)
    {
        $this->value = $value;
        $this->key = $key;
    }
    public function __toString() : string
    {
        $value = '';
        if ($this->key !== null) {
            $value .= $this->key . '=';
        }
        if (\is_array($this->value)) {
            foreach ($this->value as $singleValue) {
                $value .= $singleValue;
            }
        } else {
            $value .= $this->value;
        }
        return $value;
    }
}
