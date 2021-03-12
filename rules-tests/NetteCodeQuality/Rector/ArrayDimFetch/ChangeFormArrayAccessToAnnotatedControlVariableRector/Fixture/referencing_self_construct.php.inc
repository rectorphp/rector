<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

final class ExternalSelfConstruct extends Form
{
    public function __construct()
    {
        $this->addText('name');
    }

    public function anotherMethod()
    {
        if (! empty($this['name']->value)) {
            return;
        }
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

final class ExternalSelfConstruct extends Form
{
    public function __construct()
    {
        $this->addText('name');
    }

    public function anotherMethod()
    {
        /** @var \Nette\Forms\Controls\TextInput $nameControl */
        $nameControl = $this['name'];
        if (! empty($nameControl->value)) {
            return;
        }
    }
}

?>
