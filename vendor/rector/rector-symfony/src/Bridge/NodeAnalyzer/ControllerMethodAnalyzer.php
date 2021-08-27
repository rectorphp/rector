<?php

declare (strict_types=1);
namespace Rector\Symfony\Bridge\NodeAnalyzer;

use RectorPrefix20210827\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
final class ControllerMethodAnalyzer
{
    /**
     * @var \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver
     */
    private $parentClassScopeResolver;
    public function __construct(\Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver $parentClassScopeResolver)
    {
        $this->parentClassScopeResolver = $parentClassScopeResolver;
    }
    /**
     * Detect if is <some>Action() in Controller
     */
    public function isAction(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        $parentClassName = (string) $this->parentClassScopeResolver->resolveParentClassName($node);
        if (\RectorPrefix20210827\Nette\Utils\Strings::endsWith($parentClassName, 'Controller')) {
            return \true;
        }
        if (\RectorPrefix20210827\Nette\Utils\Strings::endsWith((string) $node->name, 'Action')) {
            return \true;
        }
        return $node->isPublic();
    }
}
