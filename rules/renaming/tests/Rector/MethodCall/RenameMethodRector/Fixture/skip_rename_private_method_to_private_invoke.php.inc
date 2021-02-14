<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

final class SkipRenamePrivateMethodToPrivateInvoke
{
    private function notify()
    {
        return 5;
    }

}

?>
