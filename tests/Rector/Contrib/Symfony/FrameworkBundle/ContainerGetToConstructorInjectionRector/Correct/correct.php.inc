<?php declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

final class MyCommand extends ContainerAwareCommand
{
    /**
     * @var \stdClass
     */
    private $stdClass;
    public function __construct(\stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
    }
    protected function execute()
    {
        $someService = $this->stdClass;
    }
}
