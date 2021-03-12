<?php

namespace Rector\Nette\Tests\Rector\MethodCall\AddNextrasDatePickerToDateControlRector\Fixture;

use Nette\Application\UI\Form;

class AddRuleChainCalls
{
    public function run()
    {
        $form = new Form();
        $form->addDatePicker('key', 'Label')
            ->addRule('...')
            ->addContitionOn('...');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\MethodCall\AddNextrasDatePickerToDateControlRector\Fixture;

use Nette\Application\UI\Form;

class AddRuleChainCalls
{
    public function run()
    {
        $form = new Form();
        $keyDateControl = $form['key'] = new \Nextras\FormComponents\Controls\DateControl('Label');
        $keyDateControl->addRule('...')
            ->addContitionOn('...');
    }
}

?>
