<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\DocBlockAnalyzer;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\TypeContext;
use Rector\NodeTypeResolver\UseStatements;

final class PropertyTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    public function __construct(TypeContext $typeContext, DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->typeContext = $typeContext;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
    }

    public function getNodeClass(): string
    {
        return Property::class;
    }

    /**
     * @param Property $propertyNode
     */
    public function resolve(Node $propertyNode): ?string
    {
        $propertyName = $propertyNode->props[0]->name->toString();
        $propertyType = $this->typeContext->getTypeForProperty($propertyName);
        if ($propertyType) {
            return $propertyType;
        }

        $propertyType = $this->docBlockAnalyzer->getAnnotationFromNode($propertyNode, 'var');

        $namespace = (string) $propertyNode->getAttribute(Attribute::NAMESPACE);
        $useStatements = $propertyNode->getAttribute(Attribute::USE_STATEMENTS);

        $propertyType = $this->resolveTypeWithNamespaceAndUseStatments($propertyType, $namespace, $useStatements);

        $this->typeContext->addPropertyType($propertyName, $propertyType);

        return $propertyType;
    }

    private function resolveTypeWithNamespaceAndUseStatments(
        string $type,
        string $namespace,
        UseStatements $useStatements
    ): string {
        foreach ($useStatements->getUseStatements() as $useStatement) {
            if (Strings::endsWith($useStatement, '\\' . $type)) {
                return $useStatement;
            }
        }

        return ($namespace ? $namespace . '\\' : '') . $type;
    }
}
