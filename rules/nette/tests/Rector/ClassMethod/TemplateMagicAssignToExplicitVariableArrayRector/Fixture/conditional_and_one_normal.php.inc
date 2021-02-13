<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class ConditionalAndOneNormalVariables extends Control
{
    public function render()
    {
        if (mt_rand(0, 1000)) {
            $this->template->yes = 'true';
        }

        $this->template->normalVariable = 'include me!';
        $this->template->render(__DIR__ . '/someFile.latte');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class ConditionalAndOneNormalVariables extends Control
{
    public function render()
    {
        if (mt_rand(0, 1000)) {
            $this->template->yes = 'true';
        }
        $this->template->render(__DIR__ . '/someFile.latte', ['normalVariable' => 'include me!']);
    }
}

?>
