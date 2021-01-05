<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser;

use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PhpdocParserPrinter\Contract\PhpDocNodeFactoryInterface;

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

    /**
     * @var PhpDocNodeFactoryInterface[]
     */
    private $phpDocNodeFactories = [];

    /**
     * @param PhpDocNodeFactoryInterface[] $phpDocNodeFactories
     */
    public function __construct(
        CurrentNodeProvider $currentNodeProvider,
        ClassAnnotationMatcher $classAnnotationMatcher,
        array $phpDocNodeFactories
    ) {
        $this->currentNodeProvider = $currentNodeProvider;
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->phpDocNodeFactories = $phpDocNodeFactories;
    }

    public function match(string $tag): ?PhpDocNodeFactoryInterface
    {
        $currentPhpNode = $this->currentNodeProvider->getNode();
        if ($currentPhpNode === null) {
            throw new ShouldNotHappenException();
        }

        $resolvedTag = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($tag, $currentPhpNode);

        foreach ($this->phpDocNodeFactories as $phpDocNodeFactory) {
            if (! $phpDocNodeFactory->isMatch($resolvedTag)) {
                continue;
            }

            return $phpDocNodeFactory;
        }

        return null;
    }
}
