<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

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

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

use Nette\Application\UI\Presenter;

final class RemovePublicPresenterMethodWithDummyAnnotation extends Presenter
{
}

?>
