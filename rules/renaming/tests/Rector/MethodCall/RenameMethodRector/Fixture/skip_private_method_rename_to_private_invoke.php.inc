<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

final class SkipPrivateMethodRenameToPrivateInvoke
{
    private function notify()
    {
        return 5;
    }

}

?>
