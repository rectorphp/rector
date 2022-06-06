<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\New_;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Rector\NodeRemoval\NodeRemover;
use RectorPrefix20220606\Rector\Symfony\NodeAnalyzer\FormType\CreateFormTypeOptionsArgMover;
use RectorPrefix20220606\Rector\Symfony\NodeAnalyzer\FormType\FormTypeClassResolver;
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
    public function __construct(CreateFormTypeOptionsArgMover $createFormTypeOptionsArgMover, NodeFactory $nodeFactory, FormTypeClassResolver $formTypeClassResolver, BetterNodeFinder $betterNodeFinder, NodeRemover $nodeRemover)
    {
        $this->createFormTypeOptionsArgMover = $createFormTypeOptionsArgMover;
        $this->nodeFactory = $nodeFactory;
        $this->formTypeClassResolver = $formTypeClassResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeRemover = $nodeRemover;
    }
    public function processNewInstance(MethodCall $methodCall, int $position, int $optionsPosition) : ?MethodCall
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
        if ($formNew instanceof New_ && $formNew->getArgs() !== []) {
            $methodCall = $this->createFormTypeOptionsArgMover->moveArgumentsToOptions($methodCall, $position, $optionsPosition, $formClassName, $formNew->getArgs());
            if (!$methodCall instanceof MethodCall) {
                throw new ShouldNotHappenException();
            }
        }
        // remove previous assign
        $previousAssign = $this->betterNodeFinder->findPreviousAssignToExpr($argValue);
        if ($previousAssign instanceof Assign) {
            $this->nodeRemover->removeNode($previousAssign);
        }
        $classConstFetch = $this->nodeFactory->createClassConstReference($formClassName);
        $currentArg = $methodCall->getArgs()[$position];
        $currentArg->value = $classConstFetch;
        return $methodCall;
    }
    private function resolveFormNew(Expr $expr) : ?New_
    {
        if ($expr instanceof New_) {
            return $expr;
        }
        if ($expr instanceof Variable) {
            $previousAssign = $this->betterNodeFinder->findPreviousAssignToExpr($expr);
            if (!$previousAssign instanceof Assign) {
                return null;
            }
            if ($previousAssign->expr instanceof New_) {
                return $previousAssign->expr;
            }
        }
        return null;
    }
}
