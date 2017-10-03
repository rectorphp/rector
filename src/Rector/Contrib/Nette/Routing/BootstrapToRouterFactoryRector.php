<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Routing;

use PhpParser\Node;
use Rector\FileSystem\CurrentFileProvider;
use Rector\Rector\AbstractRector;

final class BootstrapToRouterFactoryRector extends AbstractRector
{
    /**
     * @var CurrentFileProvider
     */
    private $currentFileProvider;

    public function __construct(CurrentFileProvider $currentFileProvider)
    {
        $this->currentFileProvider = $currentFileProvider;
    }

    public function isCandidate(Node $node): bool
    {
        dump($this->currentFileProvider->getCurrentFile());

        // current file bootstrap.php

        // TODO: Implement isCandidate() method.
    }

    public function refactor(Node $node): ?Node
    {
        // extract routing part
        // store it to app/Router/RouterFactory.php
        // TODO: Implement refactor() method.
    }
}
