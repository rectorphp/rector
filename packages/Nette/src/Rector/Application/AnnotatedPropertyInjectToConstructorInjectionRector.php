<?php declare(strict_types=1);

namespace Rector\Nette\Rector\Application;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\VarLikeIdentifier;
use Rector\BetterPhpDocParser\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AnnotatedPropertyInjectToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var string
     */
    private $annotation;

    public function __construct(
        ClassPropertyCollector $classPropertyCollector,
        DocBlockAnalyzer $docBlockAnalyzer,
        NodeTypeResolver $nodeTypeResolver,
        string $annotation = 'inject'
    ) {
        $this->classPropertyCollector = $classPropertyCollector;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->annotation = $annotation;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Property) {
            return false;
        }

        return $this->docBlockAnalyzer->hasTag($node, $this->annotation);
    }

    /**
     * @param Property $propertyNode
     */
    public function refactor(Node $propertyNode): Node
    {
        $this->docBlockAnalyzer->removeTagFromNode($propertyNode, $this->annotation);

        $propertyNode->flags = Class_::MODIFIER_PRIVATE;

        $this->addPropertyToCollector($propertyNode);

        return $propertyNode;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns public properties with ' . $this->annotation . ' to private properties and constructor injection',
            [
                new CodeSample(
                    sprintf(
                    <<<'CODE_SAMPLE'
/**
 * @var SomeService
 * @%s 
 */
public $someService;
CODE_SAMPLE
                ,
                    $this->annotation
                    ),
                    <<<'CODE_SAMPLE'
/**
 * @var SomeService
 */
private $someService;

public function __construct(SomeService $someService)
{
    $this->someService = $someService;
}
CODE_SAMPLE
                ),
            ]
        );
    }

    private function addPropertyToCollector(Property $propertyNode): void
    {
        $propertyTypes = $this->nodeTypeResolver->resolve($propertyNode);

        /** @var PropertyProperty $propertyPropertyNode */
        $propertyPropertyNode = $propertyNode->props[0];

        /** @var VarLikeIdentifier $varLikeIdentifierNode */
        $varLikeIdentifierNode = $propertyPropertyNode->name;

        $propertyName = $varLikeIdentifierNode->name;

        $this->classPropertyCollector->addPropertyForClass(
            (string) $propertyNode->getAttribute(Attribute::CLASS_NAME),
            $propertyTypes,
            $propertyName
        );
    }
}
