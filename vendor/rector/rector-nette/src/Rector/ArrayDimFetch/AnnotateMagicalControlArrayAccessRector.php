<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Unset_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\ArrayDimFetchRenamer;
use Rector\Nette\Naming\NetteControlNaming;
use Rector\Nette\NodeAnalyzer\ArrayDimFetchAnalyzer;
use Rector\Nette\NodeAnalyzer\AssignAnalyzer;
use Rector\Nette\NodeAnalyzer\ControlDimFetchAnalyzer;
use Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector\AnnotateMagicalControlArrayAccessRectorTest
 */
final class AnnotateMagicalControlArrayAccessRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;
    /**
     * @var \Rector\Naming\ArrayDimFetchRenamer
     */
    private $arrayDimFetchRenamer;
    /**
     * @var \Rector\Nette\NodeAnalyzer\ArrayDimFetchAnalyzer
     */
    private $arrayDimFetchAnalyzer;
    /**
     * @var \Rector\Nette\NodeAnalyzer\ControlDimFetchAnalyzer
     */
    private $controlDimFetchAnalyzer;
    /**
     * @var \Rector\Nette\Naming\NetteControlNaming
     */
    private $netteControlNaming;
    /**
     * @var \Rector\Nette\NodeAnalyzer\AssignAnalyzer
     */
    private $assignAnalyzer;
    public function __construct(\Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver, \Rector\Naming\ArrayDimFetchRenamer $arrayDimFetchRenamer, \Rector\Nette\NodeAnalyzer\ArrayDimFetchAnalyzer $arrayDimFetchAnalyzer, \Rector\Nette\NodeAnalyzer\ControlDimFetchAnalyzer $controlDimFetchAnalyzer, \Rector\Nette\Naming\NetteControlNaming $netteControlNaming, \Rector\Nette\NodeAnalyzer\AssignAnalyzer $assignAnalyzer)
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
        $this->arrayDimFetchRenamer = $arrayDimFetchRenamer;
        $this->arrayDimFetchAnalyzer = $arrayDimFetchAnalyzer;
        $this->controlDimFetchAnalyzer = $controlDimFetchAnalyzer;
        $this->netteControlNaming = $netteControlNaming;
        $this->assignAnalyzer = $assignAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\ArrayDimFetch::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change magic $this["some_component"] to variable assign with @var annotation', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class SomePresenter extends Presenter
{
    public function run()
    {
        if ($this['some_form']->isSubmitted()) {
        }
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class SomePresenter extends Presenter
{
    public function run()
    {
        /** @var \Nette\Application\UI\Form $someForm */
        $someForm = $this['some_form'];
        if ($someForm->isSubmitted()) {
        }
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ArrayDimFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $controlName = $this->controlDimFetchAnalyzer->matchNameOnControlVariable($node);
        if ($controlName === null) {
            return null;
        }
        // probably multiplier factory, nothing we can do... yet
        if (\strpos($controlName, '-') !== \false) {
            return null;
        }
        $variableName = $this->netteControlNaming->createVariableName($controlName);
        $controlObjectType = $this->resolveControlType($node, $controlName);
        if ($controlObjectType === null) {
            return null;
        }
        $this->assignAnalyzer->addAssignExpressionForFirstCase($variableName, $node, $controlObjectType);
        $classMethod = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if ($classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $this->arrayDimFetchRenamer->renameToVariable($classMethod, $node, $variableName);
        }
        return new \PhpParser\Node\Expr\Variable($variableName);
    }
    private function shouldSkip(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : bool
    {
        if ($this->arrayDimFetchAnalyzer->isBeingAssignedOrInitialized($arrayDimFetch)) {
            return \true;
        }
        $parent = $arrayDimFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Expr\Isset_ && !$parent instanceof \PhpParser\Node\Stmt\Unset_) {
            return \false;
        }
        return !$arrayDimFetch->dim instanceof \PhpParser\Node\Expr\Variable;
    }
    private function resolveControlType(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch, string $controlName) : ?\PHPStan\Type\ObjectType
    {
        $controlTypes = $this->methodNamesByInputNamesResolver->resolveExpr($arrayDimFetch);
        if ($controlTypes === []) {
            return null;
        }
        if (!isset($controlTypes[$controlName])) {
            return null;
        }
        return new \PHPStan\Type\ObjectType($controlTypes[$controlName]);
    }
}
