<?php declare (strict_types=1);

class SomeClass
{
    /**
     * @var Nette\Bridges\ApplicationLatte\Template
     */
    private $template;

    public function action()
    {
        $this->template->registerHelper('value', 'callback');
    }
}
