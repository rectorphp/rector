<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\GeneratedValue;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Exception\ShouldNotHappenException;

final class GeneratedValuePhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getName(): string
    {
        return GeneratedValueTagValueNode::SHORT_NAME;
    }

    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        /** @var GeneratedValue|null $generatedValue */
        $generatedValue = $this->nodeAnnotationReader->readPropertyAnnotation($node, GeneratedValue::class);
        if ($generatedValue === null) {
            return null;
        }

        return new GeneratedValueTagValueNode($generatedValue->strategy);
    }
}
