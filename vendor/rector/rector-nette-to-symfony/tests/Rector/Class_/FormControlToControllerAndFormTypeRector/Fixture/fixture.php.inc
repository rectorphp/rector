<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\FormControlToControllerAndFormTypeRector\Fixture;

use Nette\Application\UI\Form;
use Nette\Application\UI\Control;

final class SomeForm extends Control
{
    public function createComponentForm()
    {
        $form = new Form();
        $form->addText('name', 'Your name');

        $form->onSuccess[] = [$this, 'processForm'];
    }

    public function processForm(Form $form)
    {
        // process me
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\FormControlToControllerAndFormTypeRector\Fixture;

use Nette\Application\UI\Form;
use Nette\Application\UI\Control;

final class SomeFormType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['label' => 'Your name']);
    }
}

?>
