<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;

class SelfConstruct extends Form
{
    public function __construct()
    {
        $this->addText('name');
        $this['name']->value = 5;
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;

class SelfConstruct extends Form
{
    public function __construct()
    {
        $this->addText('name');
        /** @var \Nette\Forms\Controls\TextInput $nameControl */
        $nameControl = $this['name'];
        $nameControl->value = 5;
    }
}

?>
