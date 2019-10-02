<?php

namespace Rector\Symfony\Tests\Rector\Form\FormIsValidRector\Fixture;

use Symfony\Component\Form\Form;

class SkipNegativeIfsController
{
    public function action()
    {
        $form = new Form();

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->doSomething();
        }
    }
}

?>
    -----
<?php

namespace Rector\Symfony\Tests\Rector\Form\FormIsValidRector\Fixture;

use Symfony\Component\Form\Form;

class SkipNegativeIfsController
{
    public function action()
    {
        $form = new Form();

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->doSomething();
        }
    }
}

?>
