<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\AnnotationReader\NodeAnnotationReader;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocParser\AnnotationContentResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractPhpDocNodeFactory implements PhpDocNodeFactoryInterface
{
    /**
     * @var NodeAnnotationReader
     */
    protected $nodeAnnotationReader;

    /**
     * @var AnnotationContentResolver
     */
    protected $annotationContentResolver;

    /**
     * @required
     */
    public function autowireAbstractPhpDocNodeFactory(
        NodeAnnotationReader $nodeAnnotationReader,
        AnnotationContentResolver $annotationContentResolver
    ): void {
        $this->nodeAnnotationReader = $nodeAnnotationReader;
        $this->annotationContentResolver = $annotationContentResolver;
    }

    protected function resolveContentFromTokenIterator(TokenIterator $tokenIterator): string
    {
        return $this->annotationContentResolver->resolveFromTokenIterator($tokenIterator);
    }

    protected function resolveFqnTargetEntity(string $targetEntity, Node $node): string
    {
        if (class_exists($targetEntity)) {
            return $targetEntity;
        }

        $namespacedTargetEntity = $node->getAttribute(AttributeKey::NAMESPACE_NAME) . '\\' . $targetEntity;
        if (class_exists($namespacedTargetEntity)) {
            return $namespacedTargetEntity;
        }

        // probably tested class
        return $targetEntity;
    }

    /**
     * Covers spaces like https://github.com/rectorphp/rector/issues/2110
     * @return string[]
     */
    protected function matchCurlyBracketOpeningAndClosingSpace(string $annotationContent): array
    {
        $match = Strings::match($annotationContent, '#^\{(?<opening_space>\s+)#');
        $openingSpace = $match['opening_space'] ?? '';

        $match = Strings::match($annotationContent, '#(?<closing_space>\s+)\}$#');
        $closingSpace = $match['closing_space'] ?? '';

        return [$openingSpace, $closingSpace];
    }
}
