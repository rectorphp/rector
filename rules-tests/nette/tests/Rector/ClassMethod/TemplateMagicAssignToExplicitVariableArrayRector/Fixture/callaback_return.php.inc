<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class CallbackReturn extends Control
{
    public function render()
    {
        $this->template->maybe = 'true';

        $values = function () {
            return 100;
        };

        $this->template->definitely = 'true';

        $this->template->render(__DIR__ . '/someFile.latte');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class CallbackReturn extends Control
{
    public function render()
    {
        $values = function () {
            return 100;
        };

        $this->template->render(__DIR__ . '/someFile.latte', ['maybe' => 'true', 'definitely' => 'true']);
    }
}

?>
