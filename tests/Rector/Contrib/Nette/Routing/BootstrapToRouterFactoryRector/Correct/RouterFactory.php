<?php declare(strict_types=1);

final class RouterFactory
{
    public function create(): Nette\Application\Routers\RouterList
    {
        $router = new Nette\Application\Routers\RouterList;
        $router[] = new Nette\Application\Routers\Route('index', 'Page:default');

        return $router;
    }
}
