<?php declare(strict_types=1);

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
    public function getName(): string
    {
        return SensioTemplateTagValueNode::SHORT_NAME;
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
        $template = $this->nodeAnnotationReader->readMethodAnnotation($node, SensioTemplateTagValueNode::CLASS_NAME);
        if ($template === null) {
            return null;
        }

        return new SensioTemplateTagValueNode(
            $template->getTemplate(),
            $template->getOwner(),
            $template->getVars()
        );
    }
}
