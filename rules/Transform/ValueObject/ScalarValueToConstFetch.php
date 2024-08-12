<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
final class ScalarValueToConstFetch
{
    /**
     * @readonly
     * @var \PhpParser\Node\Scalar\DNumber|\PhpParser\Node\Scalar\String_|\PhpParser\Node\Scalar\LNumber
     */
    private $scalar;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch
     */
    private $constFetch;
    /**
     * @param \PhpParser\Node\Scalar\DNumber|\PhpParser\Node\Scalar\String_|\PhpParser\Node\Scalar\LNumber $scalar
     * @param \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch $constFetch
     */
    public function __construct($scalar, $constFetch)
    {
        $this->scalar = $scalar;
        $this->constFetch = $constFetch;
    }
    /**
     * @return \PhpParser\Node\Scalar\DNumber|\PhpParser\Node\Scalar\String_|\PhpParser\Node\Scalar\LNumber
     */
    public function getScalar()
    {
        return $this->scalar;
    }
    /**
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch
     */
    public function getConstFetch()
    {
        return $this->constFetch;
    }
}
