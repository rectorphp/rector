<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use Rector\Node\Attribute;

/**
 * Inspired by https://github.com/Roave/BetterReflection/blob/master/src/TypesFinder/PhpDocumentor/NamespaceNodeToReflectionTypeContext.php
 */
final class NamespaceAnalyzer
{
    public function isUseStatmenetAlreadyPresent(Node $node, string $useName): bool
    {
        /** @var Use_[] $useNodes */
        $useNodes = $node->getAttribute(Attribute::USE_NODES);
        if ($useNodes === null) {
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
        /** @var Use_[] $useNodes */
        $useNodes = $node->getAttribute(Attribute::USE_NODES);

        foreach ($useNodes as $useNode) {
            $nodeUseName = $useNode->uses[0]->name->toString();

            foreach ($types as $type) {
                if (Strings::endsWith($nodeUseName, '\\' . $type)) {
                    return $nodeUseName;
                }
            }
        }

        $namespace = $node->getAttribute(Attribute::NAMESPACE_NAME);

        return ($namespace ? $namespace . '\\' : '') . array_pop($types);
    }
}
