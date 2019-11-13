<?php

namespace Rector\Nette\Tests\Rector\MethodCall\AddDatePickerToDateControlRector\Fixture;

use Nette\Application\UI\Form;

class AssignedValue
{
    public function run()
    {
        $form = new Form();
        $datePicker = $form->addDatePicker('key', 'Label');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\MethodCall\AddDatePickerToDateControlRector\Fixture;

use Nette\Application\UI\Form;

class AssignedValue
{
    public function run()
    {
        $form = new Form();
        $datePicker = $form['key'] = new \Nextras\FormComponents\Controls\DateControl('Label');
    }
}

?>
