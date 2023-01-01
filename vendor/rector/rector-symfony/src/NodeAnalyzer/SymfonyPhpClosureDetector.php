<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Name\FullyQualified;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix202301\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
final class SymfonyPhpClosureDetector
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function detect(Closure $closure) : bool
    {
        if (\count($closure->params) !== 1) {
            return \false;
        }
        $firstParam = $closure->params[0];
        if (!$firstParam->type instanceof FullyQualified) {
            return \false;
        }
        return $this->nodeNameResolver->isName($firstParam->type, ContainerConfigurator::class);
    }
}
