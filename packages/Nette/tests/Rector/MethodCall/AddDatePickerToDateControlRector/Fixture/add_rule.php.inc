<?php

namespace Rector\Nette\Tests\Rector\MethodCall\AddDatePickerToDateControlRector\Fixture;

use Nette\Application\UI\Form;

class AddRule
{
    public function run()
    {
        $form = new Form();
        $form->addDatePicker('key', 'Label')
            ->addRule('...');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\MethodCall\AddDatePickerToDateControlRector\Fixture;

use Nette\Application\UI\Form;

class AddRule
{
    public function run()
    {
        $form = new Form();
        ($form['key'] = new \Nextras\FormComponents\Controls\DateControl('Label'))
            ->addRule('...');
    }
}

?>
