<?php

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\ParentClassWithGetTrait;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class TestController
{
    public function testAction(string $company, Request $request)
    {
        // Something went wrong: create an error in the form
        $error = new FormError($this->get('translator')->trans('error.network', [], 'errors'));

        return [];
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\ParentClassWithGetTrait;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class TestController
{
    /**
     * @var \Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface
     */
    private $someTranslator;
    public function __construct(\Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface $someTranslator)
    {
        $this->someTranslator = $someTranslator;
    }
    public function testAction(string $company, Request $request)
    {
        // Something went wrong: create an error in the form
        $error = new FormError($this->someTranslator->trans('error.network', [], 'errors'));

        return [];
    }
}

?>
