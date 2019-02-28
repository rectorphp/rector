<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Reflection;
use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeDecoratorInterface;
use Rector\NodeTypeResolver\Node\Attribute;
use ReflectionClass;

/**
 * Changes @Inject name to @Full\Namespace\Inject
 */
final class FqnAnnotationTypeDecorator implements PhpDocNodeDecoratorInterface
{
    public function decorate(AttributeAwarePhpDocNode $attributeAwarePhpDocNode, Node $node): AttributeAwarePhpDocNode
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
            $tagFqnName = $this->resolveTagFqnName($node, $tagShortName);

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
