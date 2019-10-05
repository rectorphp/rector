<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\PHPDI;

use DI\Annotation\Inject;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\PHPDI\PHPDIInjectTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;

final class PHPDIInjectPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getClass(): string
    {
        return Inject::class;
    }

    /**
     * @return PHPDIInjectTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            return null;
        }

        /** @var Inject|null $inject */
        $inject = $this->nodeAnnotationReader->readPropertyAnnotation($node, $this->getClass());
        if ($inject === null) {
            return null;
        }

        // needed for proper doc block formatting
        $this->resolveContentFromTokenIterator($tokenIterator);

        return new PHPDIInjectTagValueNode($inject->getName());
    }
}
