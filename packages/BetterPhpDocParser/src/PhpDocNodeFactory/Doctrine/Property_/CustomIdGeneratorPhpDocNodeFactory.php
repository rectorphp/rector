<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\CustomIdGenerator;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\CustomIdGeneratorTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Exception\ShouldNotHappenException;

final class CustomIdGeneratorPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getClass(): string
    {
        return CustomIdGenerator::class;
    }

    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        /** @var CustomIdGenerator|null $customIdGenerator */
        $customIdGenerator = $this->nodeAnnotationReader->readPropertyAnnotation($node, $this->getClass());
        if ($customIdGenerator === null) {
            return null;
        }

        return new CustomIdGeneratorTagValueNode($customIdGenerator->class, $annotationContent);
    }
}
