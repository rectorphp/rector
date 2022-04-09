<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory\Annotations;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\Type\ShortenedIdentifierTypeNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class DoctrineAnnotationFromNewFactory
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationKeyToValuesResolver
     */
    private $doctrineAnnotationKeyToValuesResolver;
    public function __construct(\Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationKeyToValuesResolver $doctrineAnnotationKeyToValuesResolver)
    {
        $this->doctrineAnnotationKeyToValuesResolver = $doctrineAnnotationKeyToValuesResolver;
    }
    public function create(\PhpParser\Node\Expr\New_ $new) : \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
    {
        $annotationName = $this->resolveAnnotationName($new);
        $newArgs = $new->getArgs();
        if (isset($newArgs[0])) {
            $firstAnnotationArg = $newArgs[0]->value;
            $annotationKeyToValues = $this->doctrineAnnotationKeyToValuesResolver->resolveFromExpr($firstAnnotationArg);
        } else {
            $annotationKeyToValues = [];
        }
        return new \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode(new \Rector\BetterPhpDocParser\ValueObject\Type\ShortenedIdentifierTypeNode($annotationName), null, $annotationKeyToValues);
    }
    private function resolveAnnotationName(\PhpParser\Node\Expr\New_ $new) : string
    {
        $className = $new->class;
        $originalName = $className->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NAME);
        if (!$originalName instanceof \PhpParser\Node\Name) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $originalName->toString();
    }
}
