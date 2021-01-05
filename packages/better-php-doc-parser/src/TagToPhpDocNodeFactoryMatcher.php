<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser;

use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;

final class TagToPhpDocNodeFactoryMatcher
{
    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;

    public function __construct(
        CurrentNodeProvider $currentNodeProvider,
        ClassAnnotationMatcher $classAnnotationMatcher
    ) {
        $this->currentNodeProvider = $currentNodeProvider;
        $this->classAnnotationMatcher = $classAnnotationMatcher;
    }

    public function match(string $tag): ?PhpDocNodeFactoryInterface
    {
        $currentPhpNode = $this->currentNodeProvider->getNode();
        if ($currentPhpNode === null) {
            throw new ShouldNotHappenException();
        }

        $fullyQualifiedAnnotationClass = $this->classAnnotationMatcher->resolveTagFullyQualifiedName(
            $tag,
            $currentPhpNode
        );

        return $this->phpDocNodeFactories[$fullyQualifiedAnnotationClass] ?? null;
    }
}
