<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

use Nette\Application\UI\Presenter;

final class RemovePublicPresenterMethod extends Presenter
{
    public function letItGo()
    {
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

use Nette\Application\UI\Presenter;

final class RemovePublicPresenterMethod extends Presenter
{
}

?>
