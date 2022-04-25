<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v5;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\PostRector\Collector\NodesToReplaceCollector;
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
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToReplaceCollector
     */
    private $nodesToReplaceCollector;
    public function __construct(\Rector\Core\NodeManipulator\ClassDependencyManipulator $classDependencyManipulator, \Rector\PostRector\Collector\NodesToReplaceCollector $nodesToReplaceCollector)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->nodesToReplaceCollector = $nodesToReplaceCollector;
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
        $iconFactoryMethodCalls = $this->findModuleTemplateMethodCallsByName($node, 'getIconFactory');
        $pageRendererMethodCalls = $this->findModuleTemplateMethodCallsByName($node, 'getPageRenderer');
        if ([] === $iconFactoryMethodCalls && [] === $pageRendererMethodCalls) {
            return null;
        }
        if ([] !== $iconFactoryMethodCalls) {
            $this->addIconFactoryToConstructor($node);
            foreach ($iconFactoryMethodCalls as $iconFactoryMethodCall) {
                $this->nodesToReplaceCollector->addReplaceNodeWithAnotherNode($iconFactoryMethodCall, $this->nodeFactory->createPropertyFetch('this', 'iconFactory'));
            }
        }
        if ([] !== $pageRendererMethodCalls) {
            $this->addPageRendererToConstructor($node);
            foreach ($pageRendererMethodCalls as $pageRendererMethodCall) {
                $this->nodesToReplaceCollector->addReplaceNodeWithAnotherNode($pageRendererMethodCall, $this->nodeFactory->createPropertyFetch('this', 'pageRenderer'));
            }
        }
        // change the node
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
    /**
     * @return Node[]
     */
    private function findModuleTemplateMethodCallsByName(\PhpParser\Node\Stmt\Class_ $class, string $methodCallName) : array
    {
        return $this->betterNodeFinder->find($class->stmts, function (\PhpParser\Node $node) use($methodCallName) {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return \false;
            }
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Template\\ModuleTemplate'))) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node->name, $methodCallName);
        });
    }
    private function addIconFactoryToConstructor(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $this->classDependencyManipulator->addConstructorDependency($class, new \Rector\PostRector\ValueObject\PropertyMetadata('iconFactory', new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Imaging\\IconFactory'), \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE));
    }
    private function addPageRendererToConstructor(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $this->classDependencyManipulator->addConstructorDependency($class, new \Rector\PostRector\ValueObject\PropertyMetadata('pageRenderer', new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Page\\PageRenderer'), \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE));
    }
}
