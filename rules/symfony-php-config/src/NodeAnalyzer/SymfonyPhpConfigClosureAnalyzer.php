<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\NodeAnalyzer;

use PhpParser\Node\Expr\Closure;
use Rector\NodeNameResolver\NodeNameResolver;

final class SymfonyPhpConfigClosureAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isPhpConfigClosure(Closure $closure): bool
    {
        if (count($closure->params) !== 1) {
            return false;
        }

        $onlyParam = $closure->params[0];
        if ($onlyParam->type === null) {
            return false;
        }

        return $this->nodeNameResolver->isName(
            $onlyParam->type,
            'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator'
        );
    }
}
