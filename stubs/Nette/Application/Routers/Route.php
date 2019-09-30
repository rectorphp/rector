<?php declare(strict_types=1);

namespace Nette\Application\Routers;

use Nette\Application\IRouter;

if (class_exists('Nette\Application\Routers\Route')) {
    return;
}

class Route implements IRouter
{

}
