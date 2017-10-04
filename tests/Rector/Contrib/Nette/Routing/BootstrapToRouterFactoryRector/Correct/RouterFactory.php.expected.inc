<?php

declare (strict_types=1);
namespace App\Routing\RouterFactory;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouterList;
final class RouterFactory
{
    public function create() : RouterList
    {
        $router = new RouterList();
        $router[] = new Route('index', 'Page:default');
        return $router;
    }
}