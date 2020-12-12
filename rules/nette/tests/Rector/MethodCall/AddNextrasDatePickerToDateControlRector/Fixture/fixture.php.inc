<?php

namespace Rector\Nette\Tests\Rector\MethodCall\AddNextrasDatePickerToDateControlRector\Fixture;

use Nette\Application\UI\Form;

class Fixture
{
    public function run()
    {
        $form = new Form();
        $form->addDatePicker('key', 'Label');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\MethodCall\AddNextrasDatePickerToDateControlRector\Fixture;

use Nette\Application\UI\Form;

class Fixture
{
    public function run()
    {
        $form = new Form();
        $form['key'] = new \Nextras\FormComponents\Controls\DateControl('Label');
    }
}

?>
