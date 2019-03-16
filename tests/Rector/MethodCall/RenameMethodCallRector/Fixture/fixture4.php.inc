<?php

namespace Rector\Tests\Rector\MethodCall\RenameMethodCallRector\Fixture;

use Rector\Tests\Rector\MethodCall\RenameMethodCallRector\Source\ClassMethodToBeSkipped;

class ChildOfClassMethodToBeSkipped extends ClassMethodToBeSkipped
{
    private function createHtml()
    {
        $self = new ClassMethodToBeSkipped();
        $self->createHtml();
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\MethodCall\RenameMethodCallRector\Fixture;

use Rector\Tests\Rector\MethodCall\RenameMethodCallRector\Source\ClassMethodToBeSkipped;

class ChildOfClassMethodToBeSkipped extends ClassMethodToBeSkipped
{
    private function createHtml()
    {
        $self = new ClassMethodToBeSkipped();
        $self->testHtml();
    }
}

?>
