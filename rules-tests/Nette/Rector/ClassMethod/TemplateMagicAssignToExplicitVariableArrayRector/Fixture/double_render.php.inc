<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class DoubleRender extends Control
{
    public function render()
    {
        $this->template->yes = 'true';

        if (mt_rand(0, 100)) {
            $this->template->render(__DIR__ . '/first.latte');
        } else {
            $this->template->render(__DIR__ . '/second.latte');
        }
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class DoubleRender extends Control
{
    public function render()
    {
        $parameters = [];
        $parameters['yes'] = 'true';

        if (mt_rand(0, 100)) {
            $this->template->render(__DIR__ . '/first.latte', $parameters);
        } else {
            $this->template->render(__DIR__ . '/second.latte', $parameters);
        }
    }
}

?>
