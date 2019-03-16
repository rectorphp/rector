<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteFormToSymfonyFormRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\Class_\NetteFormToSymfonyFormRector\Source\NettePresenter;
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
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteFormToSymfonyFormRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\Class_\NetteFormToSymfonyFormRector\Source\NettePresenter;
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
}

?>
