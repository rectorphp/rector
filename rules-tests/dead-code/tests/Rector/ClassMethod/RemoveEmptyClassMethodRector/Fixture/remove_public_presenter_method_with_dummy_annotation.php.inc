<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

use Nette\Application\UI\Presenter;

final class RemovePublicPresenterMethodWithDummyAnnotation extends Presenter
{
    /**
     * @param int $one
     */
    public function letItGo($one)
    {
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

use Nette\Application\UI\Presenter;

final class RemovePublicPresenterMethodWithDummyAnnotation extends Presenter
{
}

?>
