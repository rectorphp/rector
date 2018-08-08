<?php declare(strict_types=1);

use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

final class MyCommand extends ContainerAwareParentClass
{
    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;
    public function __construct(\Symfony\Component\Translation\TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    protected function execute()
    {
        $someService = $this->translator;
    }
}
