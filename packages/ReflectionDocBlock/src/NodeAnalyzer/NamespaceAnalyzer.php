<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use Rector\Node\Attribute;
use Rector\Php\TypeAnalyzer;

/**
 * Inspired by https://github.com/Roave/BetterReflection/blob/master/src/TypesFinder/PhpDocumentor/NamespaceNodeToReflectionTypeContext.php
 *
 * Also similar source https://github.com/phpstan/phpstan/blob/master/src/Analyser/NameScope.php
 */
final class NamespaceAnalyzer
{
    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    public function __construct(TypeAnalyzer $typeAnalyzer)
    {
        $this->typeAnalyzer = $typeAnalyzer;
    }

    /**
     * @param string[] $types
     */
    public function resolveTypeToFullyQualified(array $types, Node $node): string
    {
        /** @var Use_[] $useNodes */
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

        $type = array_pop($types);
        if ($this->typeAnalyzer->isPhpReservedType($type)) {
            return $type;
        }

        // return \absolute values without prefixing
        if (Strings::startsWith($type, '\\')) {
            return ltrim($type, '\\');
        }

        $namespace = $node->getAttribute(Attribute::NAMESPACE_NAME);

        return ($namespace ? $namespace . '\\' : '') . $type;
    }
}
