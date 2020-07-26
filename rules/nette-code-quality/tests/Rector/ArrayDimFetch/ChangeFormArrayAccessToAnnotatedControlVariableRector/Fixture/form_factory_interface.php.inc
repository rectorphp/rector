<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class FormFactoryInterface
{
    public function run(VideoFormFactory $videoFormFactory)
    {
        $form = $videoFormFactory->create();
        $form['video']->value = '12345';
    }
}

interface VideoFormFactory
{
    public function create(): VideoForm;
}


class VideoForm extends Form
{
    public function __construct()
    {
        $this->addCheckboxList('video');
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class FormFactoryInterface
{
    public function run(VideoFormFactory $videoFormFactory)
    {
        $form = $videoFormFactory->create();
        /** @var \Nette\Forms\Controls\CheckboxList $videoControl */
        $videoControl = $form['video'];
        $videoControl->value = '12345';
    }
}

interface VideoFormFactory
{
    public function create(): VideoForm;
}


class VideoForm extends Form
{
    public function __construct()
    {
        $this->addCheckboxList('video');
    }
}

?>
