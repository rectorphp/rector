<?php

declare (strict_types=1);
namespace Rector\Symfony\Bridge\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
final class ControllerMethodAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    public function __construct(\Rector\Symfony\TypeAnalyzer\ControllerAnalyzer $controllerAnalyzer)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
    }
    /**
     * Detect if is <some>Action() in Controller
     */
    public function isAction(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        if (!$this->controllerAnalyzer->isInsideController($node)) {
            return \false;
        }
        return $node->isPublic() && !$node->isStatic();
    }
}
