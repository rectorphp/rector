<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Reflection;
use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\Node\CurrentNodeProvider;
use ReflectionClass;
use Symplify\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Symplify\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Symplify\BetterPhpDocParser\Contract\PhpDocNodeDecoratorInterface;

/**
 * Changes @Inject name to @Full\Namespace\Inject
 */
final class FqnAnnotationTypeDecorator implements PhpDocNodeDecoratorInterface
{
    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    public function __construct(CurrentNodeProvider $currentNodeProvider)
    {
        $this->currentNodeProvider = $currentNodeProvider;
    }

    public function decorate(AttributeAwarePhpDocNode $attributeAwarePhpDocNode): AttributeAwarePhpDocNode
    {
        foreach ($attributeAwarePhpDocNode->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof AttributeAwarePhpDocTagNode) {
                continue;
            }

            $tagShortName = ltrim($phpDocChildNode->name, '@');

            // probably not a class like type
            if (ctype_lower($tagShortName[0])) {
                continue;
            }

            $tagShortName = $this->joinWithValue($phpDocChildNode, $tagShortName);
            $tagFqnName = $this->resolveTagFqnName($this->currentNodeProvider->getNode(), $tagShortName);

            $phpDocChildNode->setAttribute('annotation_class', $tagFqnName);
        }

        return $attributeAwarePhpDocNode;
    }

    private function joinWithValue(PhpDocTagNode $phpDocTagNode, string $tagShortName): string
    {
        $innerValue = (string) $phpDocTagNode->value;

        if (! Strings::startsWith($innerValue, '\\')) {
            return $tagShortName;
        }

        // drop () args
        if (Strings::contains($innerValue, '(')) {
            return $tagShortName . Strings::before($innerValue, '(');
        }

        return $tagShortName . $innerValue;
    }

    private function resolveTagFqnName(Node $node, string $tagShortName): string
    {
        $className = $node->getAttribute(Attribute::CLASS_NAME);
        if (! $className) {
            return $tagShortName;
        }

        // @todo use Use[] nodes?

        return Reflection::expandClassName($tagShortName, new ReflectionClass($className));
    }
}
