<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class SomeControlWithCustomRender extends Control
{
    public function renderYolo()
    {
        $this->template->param = 'some value';
        $this->template->render(__DIR__ . '/poll.latte');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class SomeControlWithCustomRender extends Control
{
    public function renderYolo()
    {
        $this->template->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
    }
}

?>
