<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

class Customer
{
    public function actCool()
    {
        $this->saySomeThing(); // Note casing! "T"
    }

    private function saySomething() // Note casing! "t"
    {
        print 'rector rules';
    }
}

?>
