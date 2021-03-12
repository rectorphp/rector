<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class RenderAfterReturn extends Control
{
    public function render()
    {
        $this->template->maybe = 'true';

        if (mt_rand(0, 1000)) {
            return;
        }

        $this->template->definitely = 'true';

        $this->template->render(__DIR__ . '/someFile.latte');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class RenderAfterReturn extends Control
{
    public function render()
    {
        $this->template->maybe = 'true';

        if (mt_rand(0, 1000)) {
            return;
        }

        $this->template->render(__DIR__ . '/someFile.latte', ['definitely' => 'true']);
    }
}

?>
