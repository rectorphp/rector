<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v5;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.5/Deprecation-95235-PublicGetterOfServicesInModuleTemplate.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v5\SubstituteGetIconFactoryAndGetPageRendererFromModuleTemplateRector\SubstituteGetIconFactoryAndGetPageRendererFromModuleTemplateRectorTest
 */
final class SubstituteGetIconFactoryAndGetPageRendererFromModuleTemplateRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    public function __construct(\Rector\Core\NodeManipulator\ClassDependencyManipulator $classDependencyManipulator)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $iconPropertyFetch = $this->nodeFactory->createPropertyFetch('this', 'iconFactory');
        $iconFactoryMethodCalls = $this->findModuleTemplateMethodCallsByName($node, 'getIconFactory', $iconPropertyFetch);
        $pageRendererPropertyFetch = $this->nodeFactory->createPropertyFetch('this', 'pageRenderer');
        $pageRendererMethodCalls = $this->findModuleTemplateMethodCallsByName($node, 'getPageRenderer', $pageRendererPropertyFetch);
        if (!$iconFactoryMethodCalls && !$pageRendererMethodCalls) {
            return null;
        }
        if ($iconFactoryMethodCalls) {
            $this->addIconFactoryToConstructor($node);
        }
        if ($pageRendererMethodCalls) {
            $this->addPageRendererToConstructor($node);
        }
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use PageRenderer and IconFactory directly instead of getting them from the ModuleTemplate', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class MyController extends ActionController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;

    public function __construct(ModuleTemplateFactory $moduleTemplateFactory)
    {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    public function myAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->getPageRenderer()->loadRequireJsModule('Vendor/Extension/MyJsModule');
        $moduleTemplate->setContent($moduleTemplate->getIconFactory()->getIcon('some-icon', Icon::SIZE_SMALL)->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class MyController extends ActionController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected IconFactory $iconFactory;
    protected PageRenderer $pageRenderer;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        IconFactory $iconFactory,
        PageRenderer $pageRenderer
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->iconFactory = $iconFactory;
        $this->pageRenderer = $pageRenderer;
    }

    public function myAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->pageRenderer->loadRequireJsModule('Vendor/Extension/MyJsModule');
        $moduleTemplate->setContent($this->iconFactory->getIcon('some-icon', Icon::SIZE_SMALL)->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }
}
CODE_SAMPLE
)]);
    }
    private function findModuleTemplateMethodCallsByName(\PhpParser\Node\Stmt\Class_ $class, string $methodCallName, \PhpParser\Node\Expr\PropertyFetch $propertyFetch) : bool
    {
        $hasChanged = \false;
        $this->traverseNodesWithCallable($class->stmts, function (\PhpParser\Node $node) use($methodCallName, &$hasChanged, $propertyFetch) {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return null;
            }
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Template\\ModuleTemplate'))) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->name, $methodCallName)) {
                return null;
            }
            $hasChanged = \true;
            return $propertyFetch;
        });
        return $hasChanged;
    }
    private function addIconFactoryToConstructor(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata('iconFactory', new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Imaging\\IconFactory'), \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
        $this->classDependencyManipulator->addConstructorDependency($class, $propertyMetadata);
    }
    private function addPageRendererToConstructor(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata('pageRenderer', new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Page\\PageRenderer'), \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
        $this->classDependencyManipulator->addConstructorDependency($class, $propertyMetadata);
    }
}
