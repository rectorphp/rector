<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-88496-MethodGetSwitchableControllerActionsHasBeenRemoved.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v0\ConfigurationManagerAddControllerConfigurationMethodRector\ConfigurationManagerAddControllerConfigurationMethodRectorTest
 */
final class ConfigurationManagerAddControllerConfigurationMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Configuration\\AbstractConfigurationManager'))) {
            return null;
        }
        // already checked
        $classMethod = $node->getMethod('getControllerConfiguration');
        if ($classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        $this->addMethodGetControllerConfiguration($node);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add additional method getControllerConfiguration for AbstractConfigurationManager', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class MyExtbaseConfigurationManager extends AbstractConfigurationManager
{
    protected function getSwitchableControllerActions($extensionName, $pluginName)
    {
        $switchableControllerActions = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions'][$extensionName]['modules'][$pluginName]['controllers'] ?? false;
        if ( ! is_array($switchableControllerActions)) {
            $switchableControllerActions = [];
        }

        return $switchableControllerActions;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class MyExtbaseConfigurationManager extends AbstractConfigurationManager
{
    protected function getSwitchableControllerActions($extensionName, $pluginName)
    {
        $switchableControllerActions = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions'][$extensionName]['modules'][$pluginName]['controllers'] ?? false;
        if ( ! is_array($switchableControllerActions)) {
            $switchableControllerActions = [];
        }

        return $switchableControllerActions;
    }

    protected function getControllerConfiguration($extensionName, $pluginName): array
    {
        return $this->getSwitchableControllerActions($extensionName, $pluginName);
    }
}
CODE_SAMPLE
)]);
    }
    private function addMethodGetControllerConfiguration(\PhpParser\Node\Stmt\Class_ $node) : void
    {
        $methodBuilder = new \RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder('getControllerConfiguration');
        $methodBuilder->makeProtected();
        $methodBuilder->addParams([(new \RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder('extensionName'))->getNode(), (new \RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder('pluginName'))->getNode()]);
        $newMethod = $methodBuilder->getNode();
        $newMethod->returnType = new \PhpParser\Node\Identifier('array');
        $newMethod->stmts[] = new \PhpParser\Node\Stmt\Return_($this->nodeFactory->createMethodCall('this', 'getSwitchableControllerActions', [new \PhpParser\Node\Expr\Variable('extensionName'), new \PhpParser\Node\Expr\Variable('pluginName')]));
        $node->stmts[] = new \PhpParser\Node\Stmt\Nop();
        $node->stmts[] = $newMethod;
    }
}
