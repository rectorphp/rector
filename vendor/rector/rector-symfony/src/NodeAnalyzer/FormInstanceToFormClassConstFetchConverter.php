<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeRemoval\NodeRemover;
use Rector\Symfony\NodeAnalyzer\FormType\CreateFormTypeOptionsArgMover;
use Rector\Symfony\NodeAnalyzer\FormType\FormTypeClassResolver;
final class FormInstanceToFormClassConstFetchConverter
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormType\CreateFormTypeOptionsArgMover
     */
    private $createFormTypeOptionsArgMover;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormType\FormTypeClassResolver
     */
    private $formTypeClassResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    public function __construct(\Rector\Symfony\NodeAnalyzer\FormType\CreateFormTypeOptionsArgMover $createFormTypeOptionsArgMover, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\Symfony\NodeAnalyzer\FormType\FormTypeClassResolver $formTypeClassResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeRemoval\NodeRemover $nodeRemover)
    {
        $this->createFormTypeOptionsArgMover = $createFormTypeOptionsArgMover;
        $this->nodeFactory = $nodeFactory;
        $this->formTypeClassResolver = $formTypeClassResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeRemover = $nodeRemover;
    }
    public function processNewInstance(\PhpParser\Node\Expr\MethodCall $methodCall, int $position, int $optionsPosition) : ?\PhpParser\Node\Expr\MethodCall
    {
        $args = $methodCall->getArgs();
        if (!isset($args[$position])) {
            return null;
        }
        $argValue = $args[$position]->value;
        $formClassName = $this->formTypeClassResolver->resolveFromExpr($argValue);
        if ($formClassName === null) {
            return null;
        }
        $formNew = $this->resolveFormNew($argValue);
        if ($formNew instanceof \PhpParser\Node\Expr\New_ && $formNew->getArgs() !== []) {
            $methodCall = $this->createFormTypeOptionsArgMover->moveArgumentsToOptions($methodCall, $position, $optionsPosition, $formClassName, $formNew->getArgs());
            if (!$methodCall instanceof \PhpParser\Node\Expr\MethodCall) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
        }
        // remove previous assign
        $previousAssign = $this->betterNodeFinder->findPreviousAssignToExpr($argValue);
        if ($previousAssign instanceof \PhpParser\Node\Expr\Assign) {
            $this->nodeRemover->removeNode($previousAssign);
        }
        $classConstFetch = $this->nodeFactory->createClassConstReference($formClassName);
        $currentArg = $methodCall->getArgs()[$position];
        $currentArg->value = $classConstFetch;
        return $methodCall;
    }
    private function resolveFormNew(\PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr\New_
    {
        if ($expr instanceof \PhpParser\Node\Expr\New_) {
            return $expr;
        }
        if ($expr instanceof \PhpParser\Node\Expr\Variable) {
            $previousAssign = $this->betterNodeFinder->findPreviousAssignToExpr($expr);
            if (!$previousAssign instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
            if ($previousAssign->expr instanceof \PhpParser\Node\Expr\New_) {
                return $previousAssign->expr;
            }
        }
        return null;
    }
}
