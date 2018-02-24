<?php declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

final class MyCommand extends ContainerAwareCommand
{
    protected function execute()
    {
        $someService = $this->getContainer()->get('some_service');
    }
}