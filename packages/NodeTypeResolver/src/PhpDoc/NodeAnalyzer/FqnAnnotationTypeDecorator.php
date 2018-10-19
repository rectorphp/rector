<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Reflection;
use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\NodeTypeResolver\Node\Attribute;
use ReflectionClass;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class FqnAnnotationTypeDecorator
{
    public function decorate(PhpDocInfo $phpDocInfo, Node $node): void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        foreach ($phpDocNode->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            $tagShortName = ltrim($phpDocChildNode->name, '@');

            // probably not a class like type
            if (ctype_lower($tagShortName[0])) {
                continue;
            }

            $tagShortName = $this->joinWithValue($phpDocChildNode, $tagShortName);
            $tagFqnName = $this->resolveTagFqnName($node, $tagShortName);
            $phpDocChildNode->name = '@' . $tagFqnName;
        }
    }

    private function resolveTagFqnName(Node $node, string $tagShortName): string
    {
        $className = $node->getAttribute(Attribute::CLASS_NAME);
        if (! $className) {
            return $tagShortName;
        }

        return Reflection::expandClassName($tagShortName, new ReflectionClass($className));
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
}
