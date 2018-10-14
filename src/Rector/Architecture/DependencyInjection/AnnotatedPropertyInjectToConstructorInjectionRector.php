<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\DependencyInjection;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Can cover these cases:
 * - https://doc.nette.org/en/2.4/di-usage#toc-inject-annotations
 * - https://github.com/Kdyby/Autowired/blob/master/docs/en/index.md#autowired-properties
 * - http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 */
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
     * @var string
     */
    private $annotation;

    public function __construct(
        ClassPropertyCollector $classPropertyCollector,
        DocBlockAnalyzer $docBlockAnalyzer,
        string $annotation
    ) {
        $this->classPropertyCollector = $classPropertyCollector;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->annotation = $annotation;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns non-private properties with @annotation to private properties and constructor injection',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
/**
 * @var SomeService
 * @inject
 */
public $someService;
CODE_SAMPLE
                    ,
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
                    ,
                    [
                        '$annotation' => 'inject',
                    ]
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $propertyNode
     */
    public function refactor(Node $propertyNode): ?Node
    {
        if ($propertyNode->isPrivate()) {
            return null;
        }

        if (! $this->docBlockAnalyzer->hasTag($propertyNode, $this->annotation)) {
            return null;
        }

        // it needs @var tag as well, to get the type
        if (! $this->docBlockAnalyzer->hasTag($propertyNode, 'var')) {
            return null;
        }

        $this->docBlockAnalyzer->removeTagFromNode($propertyNode, $this->annotation);

        // set to private
        $propertyNode->flags = Class_::MODIFIER_PRIVATE;

        $this->addPropertyToCollector($propertyNode);

        return $propertyNode;
    }

    private function addPropertyToCollector(Property $propertyNode): void
    {
        $propertyPropertyNode = $propertyNode->props[0];
        $className = (string) $propertyPropertyNode->getAttribute(Attribute::CLASS_NAME);

        $mainPropertyType = $this->getTypes($propertyNode)[0];

        $propertyName = (string) $propertyPropertyNode->name;

        $this->classPropertyCollector->addPropertyForClass($className, [$mainPropertyType], $propertyName);
    }
}
