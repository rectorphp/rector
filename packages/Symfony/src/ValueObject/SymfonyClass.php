<?php declare(strict_types=1);

namespace Rector\Symfony\ValueObject;

final class SymfonyClass
{
    /**
     * @var string
     */
    public const COMMAND = 'Symfony\Component\Console\Command\Command';

    /**
     * @var string
     */
    public const CONTROLLER = 'Symfony\Bundle\FrameworkBundle\Controller\Controller';

    /**
     * @var string
     */
    public const ABSTRACT_CONTROLLER = 'Symfony\Bundle\FrameworkBundle\Controller\AbstractController';

    /**
     * @var string
     */
    public const CONTROLLER_TRAIT = 'Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait';

    /**
     * @var string
     */
    public const KERNEL_TEST_CASE = 'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase';

    /**
     * @var string
     */
    public const REQUEST = 'Symfony\Component\HttpFoundation\Request';

    /**
     * @var string
     */
    public const RESPONSE = 'Symfony\Component\HttpFoundation\Response';

    /**
     * @var string
     */
    public const ROUTE_ANNOTATION = 'Symfony\Component\Routing\Annotation\Route';
}
