<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRoutePhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getClass(): string
    {
        return Route::class;
    }

    /**
     * @return SymfonyRouteTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        /** @var Route|null $route */
        $route = $this->nodeAnnotationReader->readMethodAnnotation($node, $this->getClass());
        if ($route === null) {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);
        return new SymfonyRouteTagValueNode($route, $annotationContent);
    }
}
