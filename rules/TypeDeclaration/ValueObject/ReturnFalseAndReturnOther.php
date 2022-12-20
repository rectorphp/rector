<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PhpParser\Node\Stmt\Return_;
final class ReturnFalseAndReturnOther
{
    /**
     * @readonly
     * @var \PhpParser\Node\Stmt\Return_
     */
    private $falseReturn;
    /**
     * @readonly
     * @var \PhpParser\Node\Stmt\Return_
     */
    private $otherReturn;
    public function __construct(Return_ $falseReturn, Return_ $otherReturn)
    {
        $this->falseReturn = $falseReturn;
        $this->otherReturn = $otherReturn;
    }
    public function getFalseReturn() : Return_
    {
        return $this->falseReturn;
    }
    public function getOtherReturn() : Return_
    {
        return $this->otherReturn;
    }
}
