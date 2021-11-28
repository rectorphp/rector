<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\NodeManipulator;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Nette\Kdyby\ContributeEventClassResolver;
use Rector\Nette\Kdyby\ValueObject\EventClassAndClassMethod;
final class ListeningClassMethodArgumentManipulator
{
    /**
     * @var string
     */
    private const EVENT_PARAMETER_REPLACED = 'event_parameter_replaced';
    /**
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @var \Rector\Nette\Kdyby\ContributeEventClassResolver
     */
    private $contributeEventClassResolver;
    /**
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    public function __construct(\Rector\CodingStyle\Naming\ClassNaming $classNaming, \Rector\Nette\Kdyby\ContributeEventClassResolver $contributeEventClassResolver, \Rector\Core\NodeAnalyzer\ParamAnalyzer $paramAnalyzer)
    {
        $this->classNaming = $classNaming;
        $this->contributeEventClassResolver = $contributeEventClassResolver;
        $this->paramAnalyzer = $paramAnalyzer;
    }
    /**
     * @param EventClassAndClassMethod[] $classMethodsByEventClass
     */
    public function change(array $classMethodsByEventClass) : void
    {
        foreach ($classMethodsByEventClass as $classMethods) {
            // are attributes already replaced
            $classMethod = $classMethods->getClassMethod();
            $eventParameterReplaced = (bool) $classMethod->getAttribute(self::EVENT_PARAMETER_REPLACED, \false);
            if ($eventParameterReplaced) {
                continue;
            }
            $oldParams = $classMethod->params;
            $eventClass = $classMethods->getEventClass();
            $this->changeClassParamToEventClass($eventClass, $classMethod);
            // move params to getter on event
            foreach ($oldParams as $oldParam) {
                if (!$this->paramAnalyzer->isParamUsedInClassMethod($classMethod, $oldParam)) {
                    continue;
                }
                $eventGetterToVariableAssign = $this->createEventGetterToVariableMethodCall($eventClass, $oldParam);
                $expression = new \PhpParser\Node\Stmt\Expression($eventGetterToVariableAssign);
                $classMethod->stmts = \array_merge([$expression], (array) $classMethod->stmts);
            }
            $classMethod->setAttribute(self::EVENT_PARAMETER_REPLACED, \true);
        }
    }
    private function changeClassParamToEventClass(string $eventClass, \PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $paramName = $this->classNaming->getVariableName($eventClass);
        $eventVariable = new \PhpParser\Node\Expr\Variable($paramName);
        $param = new \PhpParser\Node\Param($eventVariable, null, new \PhpParser\Node\Name\FullyQualified($eventClass));
        $classMethod->params = [$param];
    }
    private function createEventGetterToVariableMethodCall(string $eventClass, \PhpParser\Node\Param $param) : \PhpParser\Node\Expr\Assign
    {
        $paramName = $this->classNaming->getVariableName($eventClass);
        $eventVariable = new \PhpParser\Node\Expr\Variable($paramName);
        $getterMethod = $this->contributeEventClassResolver->resolveGetterMethodByEventClassAndParam($eventClass, $param);
        $methodCall = new \PhpParser\Node\Expr\MethodCall($eventVariable, $getterMethod);
        return new \PhpParser\Node\Expr\Assign($param->var, $methodCall);
    }
}
