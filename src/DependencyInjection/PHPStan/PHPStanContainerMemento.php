<?php

declare (strict_types=1);
namespace Rector\DependencyInjection\PHPStan;

use PHPStan\DependencyInjection\MemoizingContainer;
use PHPStan\DependencyInjection\Nette\NetteContainer;
use PHPStan\Parser\AnonymousClassVisitor;
use PHPStan\Parser\ArrayMapArgVisitor;
use PHPStan\Parser\RichParser;
use PHPStan\Parser\VariadicFunctionsVisitor;
use PHPStan\Parser\VariadicMethodsVisitor;
use Rector\Util\Reflection\PrivatesAccessor;
/**
 * Helper service to modify PHPStan container
 * To avoid issues caused by node replacement, like @see https://github.com/rectorphp/rector/issues/9492
 */
final class PHPStanContainerMemento
{
    public static function removeRichVisitors(RichParser $richParser): void
    {
        // the only way now seems to access container early and remove unwanted services
        // here https://github.com/phpstan/phpstan-src/blob/522421b007cbfc674bebb93e823c774167ac78cd/src/Parser/RichParser.php#L90-L92
        $privatesAccessor = new PrivatesAccessor();
        /** @var MemoizingContainer $container */
        $container = $privatesAccessor->getPrivateProperty($richParser, 'container');
        /** @var NetteContainer $originalContainer */
        $originalContainer = $privatesAccessor->getPrivateProperty($container, 'originalContainer');
        /** @var NetteContainer $originalContainer */
        $deeperContainer = $privatesAccessor->getPrivateProperty($originalContainer, 'container');
        // get tags property
        $tags = $privatesAccessor->getPrivateProperty($deeperContainer, 'tags');
        // keep visitors that are useful
        // remove all the rest, https://github.com/phpstan/phpstan-src/tree/1d86de8bb9371534983a8dbcd879e057d2ff028f/src/Parser
        $nodeVisitorsToKeep = [$container->findServiceNamesByType(AnonymousClassVisitor::class)[0] => \true, $container->findServiceNamesByType(VariadicFunctionsVisitor::class)[0] => \true, $container->findServiceNamesByType(VariadicMethodsVisitor::class)[0] => \true, $container->findServiceNamesByType(ArrayMapArgVisitor::class)[0] => \true];
        $tags[RichParser::VISITOR_SERVICE_TAG] = $nodeVisitorsToKeep;
        $privatesAccessor->setPrivateProperty($deeperContainer, 'tags', $tags);
    }
}
