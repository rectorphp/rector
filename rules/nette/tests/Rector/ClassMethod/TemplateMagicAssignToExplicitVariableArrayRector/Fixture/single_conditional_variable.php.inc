<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class SingleConditionalVariable extends Control
{
    public function render()
    {
        if (mt_rand(0, 1000)) {
            $this->template->someKey = 'true';
        } else {
            $this->template->someKey = 'false';
        }

        $this->template->render(__DIR__ . '/someFile.latte');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class SingleConditionalVariable extends Control
{
    public function render()
    {
        if (mt_rand(0, 1000)) {
            $someKey = 'true';
        } else {
            $someKey = 'false';
        }

        $this->template->render(__DIR__ . '/someFile.latte', ['someKey' => $someKey]);
    }
}

?>
