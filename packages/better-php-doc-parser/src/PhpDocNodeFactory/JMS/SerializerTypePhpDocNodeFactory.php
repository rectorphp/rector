<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\JMS;

use JMS\Serializer\Annotation\Type;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\JMS\SerializerTypeTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Core\Exception\ShouldNotHappenException;

final class SerializerTypePhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return [Type::class];
    }

    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        /** @var Type|null $type */
        $type = $this->resolveTypeAnnotation($node, $annotationClass);
        if ($type === null) {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);
        return new SerializerTypeTagValueNode($type, $annotationContent);
    }

    /**
     * Can be even ClassMethod for virtual property
     * @see https://github.com/rectorphp/rector/issues/2086
     */
    private function resolveTypeAnnotation(Node $node, string $annotationClass): ?Type
    {
        if ($node instanceof Property) {
            return $this->nodeAnnotationReader->readPropertyAnnotation($node, $annotationClass);
        }

        if ($node instanceof ClassMethod) {
            return $this->nodeAnnotationReader->readMethodAnnotation($node, $annotationClass);
        }

        throw new ShouldNotHappenException();
    }
}
