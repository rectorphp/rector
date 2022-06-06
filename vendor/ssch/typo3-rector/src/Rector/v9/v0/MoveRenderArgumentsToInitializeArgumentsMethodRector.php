<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\NodeFactory\HelperArgumentAssignFactory;
use RectorPrefix20220606\Ssch\TYPO3Rector\NodeFactory\InitializeArgumentsClassMethodFactory;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Deprecation-81213-RenderMethodArgumentOnViewHelpersDeprecated.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\MoveRenderArgumentsToInitializeArgumentsMethodRector\MoveRenderArgumentsToInitializeArgumentsMethodRectorTest
 */
final class MoveRenderArgumentsToInitializeArgumentsMethodRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\NodeFactory\HelperArgumentAssignFactory
     */
    private $helperArgumentAssignFactory;
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\NodeFactory\InitializeArgumentsClassMethodFactory
     */
    private $initializeArgumentsClassMethodFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(HelperArgumentAssignFactory $helperArgumentAssignFactory, InitializeArgumentsClassMethodFactory $initializeArgumentsClassMethodFactory, PhpDocTagRemover $phpDocTagRemover)
    {
        $this->helperArgumentAssignFactory = $helperArgumentAssignFactory;
        $this->initializeArgumentsClassMethodFactory = $initializeArgumentsClassMethodFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isAbstract()) {
            return null;
        }
        $desiredObjectTypes = [new ObjectType('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractViewHelper'), new ObjectType('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractTagBasedViewHelper'), new ObjectType('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractConditionViewHelper'), new ObjectType('TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\AbstractViewHelper'), new ObjectType('TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\AbstractTagBasedViewHelper'), new ObjectType('TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\AbstractConditionViewHelper')];
        if (!$this->nodeTypeResolver->isObjectTypes($node, $desiredObjectTypes)) {
            return null;
        }
        // Check if the ViewHelper has a render method with params, if not return immediately
        $renderMethod = $node->getMethod('render');
        if (!$renderMethod instanceof ClassMethod) {
            return null;
        }
        if ([] === $renderMethod->getParams()) {
            return null;
        }
        $this->initializeArgumentsClassMethodFactory->decorateClass($node);
        $newRenderMethodStmts = $this->helperArgumentAssignFactory->createRegisterArgumentsCalls($renderMethod);
        $renderMethod->stmts = \array_merge($newRenderMethodStmts, (array) $renderMethod->stmts);
        $this->removeParamTags($renderMethod);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Move render method arguments to initializeArguments method', [new CodeSample(<<<'CODE_SAMPLE'
class MyViewHelper implements ViewHelperInterface
{
    public function render(array $firstParameter, string $secondParameter = null)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class MyViewHelper implements ViewHelperInterface
{
    public function initializeArguments()
    {
        $this->registerArgument('firstParameter', 'array', '', true);
        $this->registerArgument('secondParameter', 'string', '', false, null);
    }

    public function render()
    {
        $firstParameter = $this->arguments['firstParameter'];
        $secondParameter = $this->arguments['secondParameter'];
    }
}
CODE_SAMPLE
)]);
    }
    private function removeParamTags(ClassMethod $classMethod) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTagRemover->removeByName($phpDocInfo, 'param');
    }
}
