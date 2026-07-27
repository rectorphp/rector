<?php

declare (strict_types=1);
namespace Rector\DependencyInjection\PHPStan;

use PhpParser\NodeVisitor;
use PHPStan\DependencyInjection\DirectExtensionsCollection;
use PHPStan\DependencyInjection\ExtensionsCollection;
use PHPStan\Parser\AnonymousClassVisitor;
use PHPStan\Parser\ArrayMapArgVisitor;
use PHPStan\Parser\RichParser;
use Rector\Util\Reflection\PrivatesAccessor;
/**
 * Helper service to modify PHPStan RichParser node visitors
 * To avoid issues caused by node replacement, like @see https://github.com/rectorphp/rector/issues/9492
 */
final class PHPStanContainerMemento
{
    public static function removeRichVisitors(RichParser $richParser): void
    {
        $privatesAccessor = new PrivatesAccessor();
        /** @var ExtensionsCollection<NodeVisitor> $nodeVisitorsCollection */
        $nodeVisitorsCollection = $privatesAccessor->getPrivateProperty($richParser, 'nodeVisitors');
        // keep visitors that are useful
        // remove all the rest, https://github.com/phpstan/phpstan-src/tree/2.2.x/src/Parser
        $nodeVisitorsToKeep = array_filter($nodeVisitorsCollection->getAll(), static fn(NodeVisitor $nodeVisitor): bool => $nodeVisitor instanceof AnonymousClassVisitor || $nodeVisitor instanceof ArrayMapArgVisitor);
        $privatesAccessor->setPrivateProperty($richParser, 'nodeVisitors', new DirectExtensionsCollection($nodeVisitorsToKeep));
    }
}
