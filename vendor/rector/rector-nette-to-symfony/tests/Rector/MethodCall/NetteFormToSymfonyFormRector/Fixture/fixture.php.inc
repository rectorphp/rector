<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\NetteFormToSymfonyFormRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\MethodCall\NetteFormToSymfonyFormRector\Source\NettePresenter;
use Nette\Application\UI\Form;

class SomePresenter extends NettePresenter
{
    public function someAction()
    {
        $form = new Form;
        $form->addText('name', 'Name:');
        // "name", "label", "cols", "maxLength"
        $form->addText('name', 'Name:', 25, 200);

        $form->addPassword('password', 'Password:');
        $form->addSubmit('login', 'Sign up');
    }

    public function selects()
    {
        $form = new Form;
        $form->addSelect('select');
        $form->addRadioList('radio_list');
        $form->addCheckboxList('checkbox_list');
        $form->addMultiSelect('multi_select');

        $form->addMultiUpload('multi_upload');
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\NetteFormToSymfonyFormRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\MethodCall\NetteFormToSymfonyFormRector\Source\NettePresenter;
use Nette\Application\UI\Form;

class SomePresenter extends NettePresenter
{
    public function someAction()
    {
        $form = $this->createFormBuilder();
        $form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['label' => 'Name:']);
        // "name", "label", "cols", "maxLength"
        $form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['label' => 'Name:']);

        $form->add('password', \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, ['label' => 'Password:']);
        $form->add('login', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, ['label' => 'Sign up']);
    }

    public function selects()
    {
        $form = $this->createFormBuilder();
        $form->add('select', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['expanded' => false, 'multiple' => false]);
        $form->add('radio_list', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['expanded' => true, 'multiple' => false]);
        $form->add('checkbox_list', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['expanded' => true, 'multiple' => true]);
        $form->add('multi_select', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['expanded' => false, 'multiple' => true]);

        $form->add('multi_upload', \Symfony\Component\Form\Extension\Core\Type\FileType::class, ['multiple' => true]);
    }
}

?>
