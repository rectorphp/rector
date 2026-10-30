<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
/**
 * @api used in deprecated ScalarValueToConstFetchRector configs
 */
final class ScalarValueToConstFetch
{
    /**
     * @readonly
     * @var \PhpParser\Node\Scalar\Float_|\PhpParser\Node\Scalar\Int_|\PhpParser\Node\Scalar\String_
     */
    private $scalar;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Expr\ConstFetch
     */
    private $constFetch;
    /**
     * @param \PhpParser\Node\Scalar\Float_|\PhpParser\Node\Scalar\Int_|\PhpParser\Node\Scalar\String_ $scalar
     * @param \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Expr\ConstFetch $constFetch
     */
    public function __construct($scalar, $constFetch)
    {
        $this->scalar = $scalar;
        $this->constFetch = $constFetch;
    }
    /**
     * @return \PhpParser\Node\Scalar\Float_|\PhpParser\Node\Scalar\Int_|\PhpParser\Node\Scalar\String_
     */
    public function getScalar()
    {
        return $this->scalar;
    }
    /**
     * @return \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Expr\ConstFetch
     */
    public function getConstFetch()
    {
        return $this->constFetch;
    }
}
