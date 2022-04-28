<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Nette\NodeAnalyzer\NetteInjectPropertyAnalyzer;
use Rector\Nette\NodeAnalyzer\PropertyUsageAnalyzer;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Covers these cases:
 * - https://doc.nette.org/en/2.4/di-usage#toc-inject-annotations
 * - https://github.com/Kdyby/Autowired/blob/master/docs/en/index.md#autowired-properties
 *
 * @see \Rector\Nette\Tests\Rector\Property\NetteInjectToConstructorInjectionRector\NetteInjectToConstructorInjectionRectorTest
 */
final class NetteInjectToConstructorInjectionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Nette\NodeAnalyzer\PropertyUsageAnalyzer
     */
    private $propertyUsageAnalyzer;
    /**
     * @readonly
     * @var \Rector\Nette\NodeAnalyzer\NetteInjectPropertyAnalyzer
     */
    private $netteInjectPropertyAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\Nette\NodeAnalyzer\PropertyUsageAnalyzer $propertyUsageAnalyzer, \Rector\Nette\NodeAnalyzer\NetteInjectPropertyAnalyzer $netteInjectPropertyAnalyzer, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector, \Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->propertyUsageAnalyzer = $propertyUsageAnalyzer;
        $this->netteInjectPropertyAnalyzer = $netteInjectPropertyAnalyzer;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns properties with `@inject` to private properties and constructor injection', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
/**
 * @var SomeService
 * @inject
 */
public $someService;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @var SomeService
 */
private $someService;

public function __construct(SomeService $someService)
{
    $this->someService = $someService;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return null;
        }
        if (!$phpDocInfo->hasByName('inject')) {
            return null;
        }
        if (!$this->netteInjectPropertyAnalyzer->canBeRefactored($node, $phpDocInfo)) {
            return null;
        }
        return $this->refactorNetteInjectProperty($phpDocInfo, $node);
    }
    private function refactorNetteInjectProperty(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Stmt\Property $property) : ?\PhpParser\Node\Stmt\Property
    {
        $injectTagNode = $phpDocInfo->getByName('inject');
        if ($injectTagNode instanceof \PHPStan\PhpDocParser\Ast\Node) {
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $injectTagNode);
        }
        $this->changePropertyVisibility($property);
        $class = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        $propertyType = $this->nodeTypeResolver->getType($property);
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $propertyType, $property->flags);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::PROPERTY_PROMOTION)) {
            $this->removeNode($property);
            return null;
        }
        return $property;
    }
    private function changePropertyVisibility(\PhpParser\Node\Stmt\Property $property) : void
    {
        if ($this->propertyUsageAnalyzer->isPropertyFetchedInChildClass($property)) {
            $this->visibilityManipulator->makeProtected($property);
        } else {
            $this->visibilityManipulator->makePrivate($property);
        }
    }
}
