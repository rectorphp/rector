<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\AnnotationReader;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;

/**
 * Keep for back compatibility, until rector packages are updated
 */
final class NodeAnnotationReader
{
    public function readAnnotation(Node $node, string $annotationClass): void
    {
        $message = sprintf('Use "%s" directly', DoctrineAnnotationTagValueNode::class);
        throw new ShouldNotHappenException($message);
    }
}
