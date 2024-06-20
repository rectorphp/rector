<?php

declare (strict_types=1);
namespace Rector\Symfony\Bridge\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
final class ControllerMethodAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    public function __construct(ControllerAnalyzer $controllerAnalyzer)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
    }
    /**
     * Detect if is <some>Action() in Controller
     */
    public function isAction(ClassMethod $classMethod) : bool
    {
        if (!$this->controllerAnalyzer->isInsideController($classMethod)) {
            return \false;
        }
        return $classMethod->isPublic() && !$classMethod->isStatic();
    }
}
