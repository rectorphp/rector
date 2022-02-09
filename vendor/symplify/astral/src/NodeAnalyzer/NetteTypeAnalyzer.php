<?php

declare (strict_types=1);
namespace RectorPrefix20220209\Symplify\Astral\NodeAnalyzer;

use RectorPrefix20220209\Nette\Application\UI\Template;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use RectorPrefix20220209\Symplify\Astral\Naming\SimpleNameResolver;
use RectorPrefix20220209\Symplify\Astral\TypeAnalyzer\ContainsTypeAnalyser;
/**
 * @api
 */
final class NetteTypeAnalyzer
{
    /**
     * @var array<class-string<Template>>
     */
    private const TEMPLATE_TYPES = ['RectorPrefix20220209\\Nette\\Application\\UI\\Template', 'RectorPrefix20220209\\Nette\\Application\\UI\\ITemplate', 'RectorPrefix20220209\\Nette\\Bridges\\ApplicationLatte\\Template', 'RectorPrefix20220209\\Nette\\Bridges\\ApplicationLatte\\DefaultTemplate'];
    /**
     * @var \Symplify\Astral\Naming\SimpleNameResolver
     */
    private $simpleNameResolver;
    /**
     * @var \Symplify\Astral\TypeAnalyzer\ContainsTypeAnalyser
     */
    private $containsTypeAnalyser;
    public function __construct(\RectorPrefix20220209\Symplify\Astral\Naming\SimpleNameResolver $simpleNameResolver, \RectorPrefix20220209\Symplify\Astral\TypeAnalyzer\ContainsTypeAnalyser $containsTypeAnalyser)
    {
        $this->simpleNameResolver = $simpleNameResolver;
        $this->containsTypeAnalyser = $containsTypeAnalyser;
    }
    /**
     * E.g. $this->template->key
     */
    public function isTemplateMagicPropertyType(\PhpParser\Node\Expr $expr, \PHPStan\Analyser\Scope $scope) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        if (!$expr->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        return $this->isTemplateType($expr->var, $scope);
    }
    /**
     * E.g. $this->template
     */
    public function isTemplateType(\PhpParser\Node\Expr $expr, \PHPStan\Analyser\Scope $scope) : bool
    {
        return $this->containsTypeAnalyser->containsExprTypes($expr, $scope, self::TEMPLATE_TYPES);
    }
    /**
     * This type has getComponent() method
     */
    public function isInsideComponentContainer(\PHPStan\Analyser\Scope $scope) : bool
    {
        $className = $this->simpleNameResolver->getClassNameFromScope($scope);
        if ($className === null) {
            return \false;
        }
        // this type has getComponent() method
        return \is_a($className, 'RectorPrefix20220209\\Nette\\ComponentModel\\Container', \true);
    }
    public function isInsideControl(\PHPStan\Analyser\Scope $scope) : bool
    {
        $className = $this->simpleNameResolver->getClassNameFromScope($scope);
        if ($className === null) {
            return \false;
        }
        return \is_a($className, 'RectorPrefix20220209\\Nette\\Application\\UI\\Control', \true);
    }
}
