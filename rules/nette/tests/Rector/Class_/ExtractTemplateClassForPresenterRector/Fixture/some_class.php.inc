<?php

namespace Rector\Nette\Tests\Rector\Class_\ExtractTemplateClassForPresenterRector\Fixture;

use Nette\Application\UI\Presenter;

final class SomePresenter extends Presenter
{
    public function render()
    {
    }
}

// some_template.latte

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\Class_\ExtractTemplateClassForPresenterRector\Fixture;

use Nette\Application\UI\Presenter;

/**
 * @property SomeTemplate $template
 */
final class SomePresenter extends Presenter
{
    public function render()
    {
    }
}

?>
