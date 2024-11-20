<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory\Annotations;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\Type\ShortenedIdentifierTypeNode;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class DoctrineAnnotationFromNewFactory
{
    /**
     * @readonly
     */
    private \Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationKeyToValuesResolver $doctrineAnnotationKeyToValuesResolver;
    public function __construct(\Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationKeyToValuesResolver $doctrineAnnotationKeyToValuesResolver)
    {
        $this->doctrineAnnotationKeyToValuesResolver = $doctrineAnnotationKeyToValuesResolver;
    }
    public function create(New_ $new) : DoctrineAnnotationTagValueNode
    {
        $annotationName = $this->resolveAnnotationName($new);
        $newArgs = $new->getArgs();
        if (isset($newArgs[0])) {
            $firstAnnotationArg = $newArgs[0]->value;
            $annotationKeyToValues = $this->doctrineAnnotationKeyToValuesResolver->resolveFromExpr($firstAnnotationArg);
        } else {
            $annotationKeyToValues = [];
        }
        return new DoctrineAnnotationTagValueNode(new ShortenedIdentifierTypeNode($annotationName), null, $annotationKeyToValues);
    }
    private function resolveAnnotationName(New_ $new) : string
    {
        $className = $new->class;
        $originalName = $className->getAttribute(AttributeKey::ORIGINAL_NAME);
        if (!$originalName instanceof Name) {
            throw new ShouldNotHappenException();
        }
        return $originalName->toString();
    }
}
