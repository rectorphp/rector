<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PhpAttribute\Exception\InvalidNestedAttributeException;
use Rector\PhpAttribute\NodeFactory\NamedArgsFactory;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @implements AnnotationToAttributeMapperInterface<DoctrineAnnotationTagValueNode>
 */
final class DoctrineAnnotationAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    private AnnotationToAttributeMapper $annotationToAttributeMapper;

    public function __construct(
        private PhpVersionProvider $phpVersionProvider,
        private NamedArgsFactory $namedArgsFactory
    ) {
    }

    /**
     * Avoid circular reference
     */
    #[Required]
    public function autowire(AnnotationToAttributeMapper $annotationToAttributeMapper): void
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
    }

    public function isCandidate(mixed $value): bool
    {
        return $value instanceof DoctrineAnnotationTagValueNode;
    }

    /**
     * @param DoctrineAnnotationTagValueNode $value
     */
    public function map($value): New_
    {
        // if PHP 8.0- throw exception
        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::NEW_INITIALIZERS)) {
            throw new InvalidNestedAttributeException();
        }

        $annotationShortName = $this->resolveAnnotationName($value);

        $values = $value->getValues();
        if ($values !== []) {
            $argValues = $this->annotationToAttributeMapper->map(
                $value->getValuesWithExplicitSilentAndWithoutQuotes()
            );

            if (! is_array($argValues)) {
                throw new ShouldNotHappenException();
            }

            $args = $this->namedArgsFactory->createFromValues($argValues);
        } else {
            $args = [];
        }

        return new New_(new Name($annotationShortName), $args);
    }

    private function resolveAnnotationName(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode): string
    {
        $annotationShortName = $doctrineAnnotationTagValueNode->identifierTypeNode->name;
        return ltrim($annotationShortName, '@');
    }
}
