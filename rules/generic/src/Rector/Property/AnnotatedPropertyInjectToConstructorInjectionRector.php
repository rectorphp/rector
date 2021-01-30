<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette\NetteInjectTagNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Rector\FamilyTree\NodeAnalyzer\PropertyUsageAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Can cover these cases:
 * - https://doc.nette.org/en/2.4/di-usage#toc-inject-annotations
 * - https://github.com/Kdyby/Autowired/blob/master/docs/en/index.md#autowired-properties
 * - http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * - https://github.com/rectorphp/rector/issues/700#issue-370301169
 *
 * @see \Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\AnnotatedPropertyInjectToConstructorInjectionRectorTest
 */
final class AnnotatedPropertyInjectToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var PropertyUsageAnalyzer
     */
    private $propertyUsageAnalyzer;

    /**
     * @var ClassChildAnalyzer
     */
    private $classChildAnalyzer;

    public function __construct(
        ClassChildAnalyzer $classChildAnalyzer,
        PropertyUsageAnalyzer $propertyUsageAnalyzer
    ) {
        $this->propertyUsageAnalyzer = $propertyUsageAnalyzer;
        $this->classChildAnalyzer = $classChildAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns non-private properties with `@inject` to private properties and constructor injection',
            [
                new CodeSample(
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
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipProperty($node)) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $phpDocInfo->removeByType(NetteInjectTagNode::class);

        if ($this->propertyUsageAnalyzer->isPropertyFetchedInChildClass($node)) {
            $this->visibilityManipulator->makeProtected($node);
        } else {
            $this->visibilityManipulator->makePrivate($node);
        }

        $this->addPropertyToCollector($node);

        if ($this->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
            $this->removeNode($node);
            return null;
        }

        return $node;
    }

    private function shouldSkipProperty(Property $property): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if (! $phpDocInfo->hasByType(NetteInjectTagNode::class)) {
            return true;
        }

        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            return true;
        }

        if (! $classLike instanceof Class_) {
            return true;
        }

        if ($classLike->isAbstract()) {
            return true;
        }

        if ($this->classChildAnalyzer->hasChildClassConstructor($classLike)) {
            return true;
        }

        if ($this->classChildAnalyzer->hasParentClassConstructor($classLike)) {
            return true;
        }

        // it needs @var tag as well, to get the type
        if ($phpDocInfo->getVarTagValueNode() !== null) {
            return false;
        }

        return $property->type === null;
    }
}
