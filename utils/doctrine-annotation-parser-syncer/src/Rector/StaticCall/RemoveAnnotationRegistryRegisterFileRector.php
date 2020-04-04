<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\StaticCall;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;

final class RemoveAnnotationRegistryRegisterFileRector extends AbstractRector implements ClassSyncerRectorInterface
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Remove AnnotationRegistry::registerFile() that is now covered by composer autoload'
        );
    }

    /**
     * @return class-string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInClassesNamed($node, [DocParser::class, AnnotationReader::class])) {
            return null;
        }

        if (! $this->isStaticCallNamed($node, AnnotationRegistry::class, 'registerFile')) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }
}
