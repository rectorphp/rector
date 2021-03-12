<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

final class ParentVarConstruct extends Control
{
    public function run()
    {
        /** @var ContestForm $contestForm */
        $contestForm = $this['whatever'];
        $contestForm['not_found']->setItems([1, 2, 3]);
    }
}

final class ContestForm extends Form
{
    public function __construct(?IContainer $parent = null, $name = null)
    {
        parent::__construct($parent, $name);

        $this->addSelect('not_found');
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

final class ParentVarConstruct extends Control
{
    public function run()
    {
        /** @var ContestForm $contestForm */
        $contestForm = $this['whatever'];
        /** @var \Nette\Forms\Controls\SelectBox $notFoundControl */
        $notFoundControl = $contestForm['not_found'];
        $notFoundControl->setItems([1, 2, 3]);
    }
}

final class ContestForm extends Form
{
    public function __construct(?IContainer $parent = null, $name = null)
    {
        parent::__construct($parent, $name);

        $this->addSelect('not_found');
    }
}

?>
