<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

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

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

use Nette\Application\UI\Presenter;

final class RemovePublicPresenterMethod extends Presenter
{
}

?>
