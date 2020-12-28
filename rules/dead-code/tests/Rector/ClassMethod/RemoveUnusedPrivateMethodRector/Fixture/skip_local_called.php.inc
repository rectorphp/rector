<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

/**
 * @see https://3v4l.org/IsSHq
 */
class SkipLocalCalled
{
    private $id;
    public static function create($id){
        $customer = new self();
        $customer->setId($id);
        return $customer;
    }
    private function setId($id){
        $this->id=$id;
    }
}
