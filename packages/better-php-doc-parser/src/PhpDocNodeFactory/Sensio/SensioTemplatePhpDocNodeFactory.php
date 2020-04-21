<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Sensio;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class SensioTemplatePhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getClass(): string
    {
        return Template::class;
    }

    /**
     * @return SensioTemplateTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        /** @var Template|null $template */
        $template = $this->nodeAnnotationReader->readMethodAnnotation($node, $this->getClass());
        if ($template === null) {
            return null;
        }

        // to skip tokens for this node
        $this->resolveContentFromTokenIterator($tokenIterator);

        return new SensioTemplateTagValueNode(
            $template->getTemplate(),
            $template->getOwner(),
            $template->getVars()
        );
    }
}
