<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer;
use Rector\Symfony\NodeAnalyzer\FormCollectionAnalyzer;
use Rector\Symfony\NodeAnalyzer\FormInstanceToFormClassConstFetchConverter;
use Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Best resource with clear example:
 *
 * @see https://stackoverflow.com/questions/34027711/passing-data-to-buildform-in-symfony-2-8-3-0
 *
 * @see https://github.com/symfony/symfony/commit/adf20c86fb0d8dc2859aa0d2821fe339d3551347
 * @see http://www.keganv.com/passing-arguments-controller-file-type-symfony-3/
 * @see https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\FormTypeInstanceToClassConstRectorTest
 */
final class FormTypeInstanceToClassConstRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormInstanceToFormClassConstFetchConverter
     */
    private $formInstanceToFormClassConstFetchConverter;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer
     */
    private $formAddMethodCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher
     */
    private $formOptionsArrayMatcher;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormCollectionAnalyzer
     */
    private $formCollectionAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    public function __construct(\Rector\Symfony\NodeAnalyzer\FormInstanceToFormClassConstFetchConverter $formInstanceToFormClassConstFetchConverter, \Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer $formAddMethodCallAnalyzer, \Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher $formOptionsArrayMatcher, \Rector\Symfony\NodeAnalyzer\FormCollectionAnalyzer $formCollectionAnalyzer, \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer $controllerAnalyzer)
    {
        $this->formInstanceToFormClassConstFetchConverter = $formInstanceToFormClassConstFetchConverter;
        $this->formAddMethodCallAnalyzer = $formAddMethodCallAnalyzer;
        $this->formOptionsArrayMatcher = $formOptionsArrayMatcher;
        $this->formCollectionAnalyzer = $formCollectionAnalyzer;
        $this->controllerAnalyzer = $controllerAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes createForm(new FormType), add(new FormType) to ones with "FormType::class"', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extends Controller
{
    public function action()
    {
        $form = $this->createForm(new TeamType);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extends Controller
{
    public function action()
    {
        $form = $this->createForm(TeamType::class);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->controllerAnalyzer->isController($node->var) && $this->isName($node->name, 'createForm')) {
            return $this->formInstanceToFormClassConstFetchConverter->processNewInstance($node, 0, 2);
        }
        if (!$this->formAddMethodCallAnalyzer->isMatching($node)) {
            return null;
        }
        // special case for collections
        if ($this->formCollectionAnalyzer->isCollectionType($node)) {
            $this->refactorCollectionOptions($node);
        }
        return $this->formInstanceToFormClassConstFetchConverter->processNewInstance($node, 1, 2);
    }
    private function refactorCollectionOptions(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        $optionsArray = $this->formOptionsArrayMatcher->match($methodCall);
        if (!$optionsArray instanceof \PhpParser\Node\Expr\Array_) {
            return;
        }
        foreach ($optionsArray->items as $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }
            if ($arrayItem->key === null) {
                continue;
            }
            if (!$this->valueResolver->isValues($arrayItem->key, ['entry', 'entry_type'])) {
                continue;
            }
            if (!$arrayItem->value instanceof \PhpParser\Node\Expr\New_) {
                continue;
            }
            $newClass = $arrayItem->value->class;
            if (!$newClass instanceof \PhpParser\Node\Name) {
                continue;
            }
            $arrayItem->value = $this->nodeFactory->createClassConstReference($newClass->toString());
        }
    }
}
