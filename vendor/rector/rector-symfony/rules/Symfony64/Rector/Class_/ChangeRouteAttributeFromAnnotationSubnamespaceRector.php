<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony64\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony64\Rector\Class_\ChangeRouteAttributeFromAnnotationSubnamespaceRector
 */
final class ChangeRouteAttributeFromAnnotationSubnamespaceRector extends AbstractRector
{
    private const ANNOTATION_ROUTE = 'Symfony\\Component\\Routing\\Annotation\\Route';
    private const ATTRIBUTE_ROUTE = 'Symfony\\Component\\Routing\\Attribute\\Route';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace Symfony\\Component\\Routing\\Annotation\\Route by Symfony\\Component\\Routing\\Attribute\\Route when the class use #Route[] attribute', [new CodeSample(<<<'CODE_SAMPLE'
    /**
     * #[\Symfony\Component\Routing\Annotation\Route("/foo")]
    */
    public function create(Request $request): Response
    {
        return new Response();
    }
CODE_SAMPLE
, <<<'CODE_SAMPLE'
    #[\Symfony\Component\Routing\Attribute\Route('/foo')]
    public function create(Request $request): Response
    {
        return new Response();
    }
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Class_::class];
    }
    /**
     * @param ClassMethod|Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($node->attrGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ($this->isSymfonyRouteAttribute($attribute)) {
                    $attribute->name = new Node\Name\FullyQualified(self::ATTRIBUTE_ROUTE);
                    return $node;
                }
            }
        }
        return null;
    }
    public function isSymfonyRouteAttribute(Node $node) : bool
    {
        return $node instanceof Attribute && $node->name !== null && (string) $node->name === self::ANNOTATION_ROUTE;
    }
}
