<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\Naming;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Analyser\NameScope;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\StaticTypeMapper;

/**
 * @see https://github.com/phpstan/phpstan-src/blob/8376548f76e2c845ae047e3010e873015b796818/src/Analyser/NameScope.php#L32
 */
final class NameScopeFactory
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * This is needed to avoid circular references
     * @required
     */
    public function autowireNameScopeFactory(PhpDocInfoFactory $phpDocInfoFactory): void
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function createNameScopeFromNodeWithoutTemplateTypes(Node $node): NameScope
    {
        $namespace = $node->getAttribute(AttributeKey::NAMESPACE_NAME);

        /** @var Use_[] $useNodes */
        $useNodes = (array) $node->getAttribute(AttributeKey::USE_NODES);

        $uses = $this->resolveUseNamesByAlias($useNodes);
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        return new NameScope($namespace, $uses, $className);
    }

    public function createNameScopeFromNode(Node $node): NameScope
    {
        $nameScope = $this->createNameScopeFromNodeWithoutTemplateTypes($node);
        $templateTypeMap = $this->templateTemplateTypeMap($node);

        return new NameScope(
            $nameScope->getNamespace(),
            $nameScope->getUses(),
            $nameScope->getClassName(),
            null,
            $templateTypeMap
        );
    }

    public function setStaticTypeMapper(StaticTypeMapper $staticTypeMapper): void
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }

    /**
     * @param Use_[] $useNodes
     * @return array<string, string>
     */
    private function resolveUseNamesByAlias(array $useNodes): array
    {
        $useNamesByAlias = [];

        foreach ($useNodes as $useNode) {
            foreach ($useNode->uses as $useUse) {
                /** @var UseUse $useUse */
                $aliasName = $useUse->getAlias()
                    ->name;

                $useName = $useUse->name->toString();
                if (! is_string($useName)) {
                    throw new ShouldNotHappenException();
                }

                // uses must be lowercase, as PHPStan lowercases it
                $lowercasedAliasName = strtolower($aliasName);

                $useNamesByAlias[$lowercasedAliasName] = $useName;
            }
        }

        return $useNamesByAlias;
    }

    private function templateTemplateTypeMap(Node $node): TemplateTypeMap
    {
        $nodeTemplateTypes = $this->resolveTemplateTypesFromNode($node);

        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        $classTemplateTypes = [];
        if ($class instanceof ClassLike) {
            $classTemplateTypes = $this->resolveTemplateTypesFromNode($class);
        }

        $templateTypes = array_merge($nodeTemplateTypes, $classTemplateTypes);
        return new TemplateTypeMap($templateTypes);
    }

    /**
     * @return Type[]
     */
    private function resolveTemplateTypesFromNode(Node $node): array
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        $templateTypes = [];
        foreach ($phpDocInfo->getTemplateTagValueNodes() as $templateTagValueNode) {
            $phpstanType = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($templateTagValueNode, $node);
            $templateTypes[$templateTagValueNode->name] = $phpstanType;
        }

        return $templateTypes;
    }
}
