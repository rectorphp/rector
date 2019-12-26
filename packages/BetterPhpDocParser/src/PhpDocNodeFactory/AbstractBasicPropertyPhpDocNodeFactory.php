<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use Doctrine\ORM\Mapping\Annotation;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;

abstract class AbstractBasicPropertyPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    protected function createFromNode(Node $node): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            return null;
        }

        /** @var Annotation|null $annotation */
        $annotation = $this->nodeAnnotationReader->readPropertyAnnotation($node, $this->getClass());
        if ($annotation === null) {
            return null;
        }

        $getTagValueNodeClass = $this->getTagValueNodeClass();
        return new $getTagValueNodeClass();
    }

    abstract protected function getTagValueNodeClass(): string;
}
