<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\Set;

use Rector\Set\Contract\SetListInterface;
final class NetteToSymfonySetList implements \Rector\Set\Contract\SetListInterface
{
    /**
     * @var string
     */
    public const CONTRIBUTTE_TO_SYMFONY = __DIR__ . '/../../config/sets/contributte-to-symfony.php';
    /**
     * @var string
     */
    public const NETTE_CONTROL_TO_SYMFONY_CONTROLLER = __DIR__ . '/../../config/sets/nette-control-to-symfony-controller.php';
    /**
     * @var string
     */
    public const NETTE_FORMS_TO_SYMFONY = __DIR__ . '/../../config/sets/nette-forms-to-symfony.php';
    /**
     * @var string
     */
    public const NETTE_TESTER_TO_PHPUNIT = __DIR__ . '/../../config/sets/nette-tester-to-phpunit.php';
    /**
     * @var string
     */
    public const NETTE_TO_SYMFONY = __DIR__ . '/../../config/sets/nette-to-symfony.php';
}
