<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Node\Attribute;

/**
 * Inspired by https://github.com/Roave/BetterReflection/blob/master/src/TypesFinder/PhpDocumentor/NamespaceNodeToReflectionTypeContext.php
 */
final class NamespaceAnalyzer
{
    public function isUseStatementAlreadyPresent(Node $node, string $useName): bool
    {
        $useNodes = $node->getAttribute(Attribute::USE_NODES);
        if (! count($useNodes)) {
            return false;
        }

        foreach ($useNodes as $useNode) {
            $nodeUseName = $useNode->uses[0]->name->toString();
            if ($nodeUseName === $useName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $types
     */
    public function resolveTypeToFullyQualified(array $types, Node $node): string
    {
        $useNodes = (array) $node->getAttribute(Attribute::USE_NODES);
        foreach ($useNodes as $useNode) {
            $useUseNode = $useNode->uses[0];
            $nodeUseName = $useUseNode->name->toString();

            foreach ($types as $type) {
                if (Strings::endsWith($nodeUseName, '\\' . $type)) {
                    return $nodeUseName;
                }

                // alias
                if ($useUseNode->getAlias() && $type === $useUseNode->getAlias()->toString()) {
                    return $nodeUseName;
                }
            }
        }

        $namespace = $node->getAttribute(Attribute::NAMESPACE_NAME);

        return ($namespace ? $namespace . '\\' : '') . array_pop($types);
    }
}
