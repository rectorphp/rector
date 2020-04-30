<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\JMS;

use JMS\DiExtraBundle\Annotation\Service;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\JMS\JMSServiceValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;

final class JMSServicePhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return [Service::class];
    }

    /**
     * @return JMSServiceValueNode|null
     */
    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        if ($node instanceof ClassMethod) {
            /** @var Service|null $service */
            $service = $this->nodeAnnotationReader->readMethodAnnotation($node, $annotationClass);
            if ($service === null) {
                return null;
            }
        } elseif ($node instanceof Class_) {
            /** @var Service|null $service */
            $service = $this->nodeAnnotationReader->readClassAnnotation($node, $annotationClass);
            if ($service === null) {
                return null;
            }
        } else {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        return new JMSServiceValueNode($service, $annotationContent);
    }
}
