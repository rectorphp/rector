<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Symfony\Validator\Constraints;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertChoiceTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Symfony\Component\Validator\Constraints\Choice;

final class AssertChoicePhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getClass(): string
    {
        return Choice::class;
    }

    /**
     * @return AssertChoiceTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            return null;
        }

        /** @var Choice|null $choice */
        $choice = $this->nodeAnnotationReader->readPropertyAnnotation($node, $this->getClass());
        if ($choice === null) {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        return new AssertChoiceTagValueNode($choice->callback, $choice->strict, $annotationContent, $choice->choices);
    }
}
