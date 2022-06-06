<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Bridge\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
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
    public function isAction(Node $node) : bool
    {
        if (!$node instanceof ClassMethod) {
            return \false;
        }
        if (!$this->controllerAnalyzer->isInsideController($node)) {
            return \false;
        }
        return $node->isPublic() && !$node->isStatic();
    }
}
