<?php

declare (strict_types=1);
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
use RectorPrefix20211123\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements AnnotationToAttributeMapperInterface<DoctrineAnnotationTagValueNode>
 */
final class DoctrineAnnotationAnnotationToAttributeMapper implements \Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface
{
    /**
     * @var \Rector\PhpAttribute\AnnotationToAttributeMapper
     */
    private $annotationToAttributeMapper;
    /**
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @var \Rector\PhpAttribute\NodeFactory\NamedArgsFactory
     */
    private $namedArgsFactory;
    public function __construct(\Rector\Core\Php\PhpVersionProvider $phpVersionProvider, \Rector\PhpAttribute\NodeFactory\NamedArgsFactory $namedArgsFactory)
    {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->namedArgsFactory = $namedArgsFactory;
    }
    /**
     * Avoid circular reference
     * @required
     */
    public function autowire(\Rector\PhpAttribute\AnnotationToAttributeMapper $annotationToAttributeMapper) : void
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
    }
    /**
     * @param mixed $value
     */
    public function isCandidate($value) : bool
    {
        return $value instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
    }
    /**
     * @param DoctrineAnnotationTagValueNode $value
     */
    public function map($value) : \PhpParser\Node\Expr\New_
    {
        // if PHP 8.0- throw exception
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::NEW_INITIALIZERS)) {
            throw new \Rector\PhpAttribute\Exception\InvalidNestedAttributeException();
        }
        $annotationShortName = $this->resolveAnnotationName($value);
        $values = $value->getValues();
        if ($values !== []) {
            $argValues = $this->annotationToAttributeMapper->map($value->getValuesWithExplicitSilentAndWithoutQuotes());
            if (!\is_array($argValues)) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $args = $this->namedArgsFactory->createFromValues($argValues);
        } else {
            $args = [];
        }
        return new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name($annotationShortName), $args);
    }
    private function resolveAnnotationName(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : string
    {
        $annotationShortName = $doctrineAnnotationTagValueNode->identifierTypeNode->name;
        return \ltrim($annotationShortName, '@');
    }
}
