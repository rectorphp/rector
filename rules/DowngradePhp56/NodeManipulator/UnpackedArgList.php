<?php

declare (strict_types=1);
namespace Rector\DowngradePhp56\NodeManipulator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
final class UnpackedArgList
{
    /**
     * @var Arg[]
     */
    private $args = [];
    /**
     * @var int
     */
    private $pointer = 0;
    /**
     * @param Arg[] $args
     */
    public function __construct(array $args = [])
    {
        foreach ($args as $arg) {
            $this->addArg($arg);
        }
    }
    /**
     * @return Arg[]
     */
    public function toArray() : array
    {
        return $this->args;
    }
    public function addArg(\PhpParser\Node\Arg $arg) : void
    {
        $this->args[$this->pointer] = $this->args[$this->pointer] ?? new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\Array_());
        if ($arg->unpack) {
            $arg->unpack = \false;
            $this->unpack($arg);
            return;
        }
        $this->addAsItem($arg);
    }
    private function unpack(\PhpParser\Node\Arg $arg) : void
    {
        if ($arg->value instanceof \PhpParser\Node\Expr\Array_) {
            foreach ($arg->value->items as $arrayItem) {
                if ($arrayItem === null) {
                    continue;
                }
                $this->addArrayItem($arrayItem);
            }
            return;
        }
        $this->addNextArg($arg);
    }
    private function addAsItem(\PhpParser\Node\Arg $arg) : void
    {
        $this->addArrayItem(new \PhpParser\Node\Expr\ArrayItem($arg->value));
    }
    private function addArrayItem(\PhpParser\Node\Expr\ArrayItem $arrayItem) : void
    {
        /** @var Array_ $array */
        $array = $this->args[$this->pointer]->value;
        $array->items[] = $arrayItem;
    }
    private function addNextArg(\PhpParser\Node\Arg $arg) : void
    {
        $this->next();
        $this->args[$this->pointer] = $arg;
        ++$this->pointer;
    }
    private function next() : void
    {
        /** @var Array_ $array */
        $array = $this->args[$this->pointer]->value;
        if ($array->items !== []) {
            ++$this->pointer;
        }
    }
}
