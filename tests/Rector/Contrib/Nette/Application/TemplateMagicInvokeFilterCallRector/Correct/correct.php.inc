<?php declare (strict_types=1);

class SomeClass
{
    /**
     * @var Nette\Bridges\ApplicationLatte\Template
     */
    private $template;

    public function action()
    {
        $this->template->getLatte()->invokeFilter('someFilter', 'value');
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}