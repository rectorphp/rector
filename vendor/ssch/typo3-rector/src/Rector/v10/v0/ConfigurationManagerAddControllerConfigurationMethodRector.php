<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Nop;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20220606\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-88496-MethodGetSwitchableControllerActionsHasBeenRemoved.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v0\ConfigurationManagerAddControllerConfigurationMethodRector\ConfigurationManagerAddControllerConfigurationMethodRectorTest
 */
final class ConfigurationManagerAddControllerConfigurationMethodRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('TYPO3\\CMS\\Extbase\\Configuration\\AbstractConfigurationManager'))) {
            return null;
        }
        // already checked
        $classMethod = $node->getMethod('getControllerConfiguration');
        if ($classMethod instanceof ClassMethod) {
            return null;
        }
        $this->addMethodGetControllerConfiguration($node);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add additional method getControllerConfiguration for AbstractConfigurationManager', [new CodeSample(<<<'CODE_SAMPLE'
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
    private function addMethodGetControllerConfiguration(Class_ $class) : void
    {
        $methodBuilder = new MethodBuilder('getControllerConfiguration');
        $methodBuilder->makeProtected();
        $methodBuilder->addParams([(new ParamBuilder('extensionName'))->getNode(), (new ParamBuilder('pluginName'))->getNode()]);
        $newMethod = $methodBuilder->getNode();
        $newMethod->returnType = new Identifier('array');
        $newMethod->stmts[] = new Return_($this->nodeFactory->createMethodCall('this', 'getSwitchableControllerActions', [new Variable('extensionName'), new Variable('pluginName')]));
        $class->stmts[] = new Nop();
        $class->stmts[] = $newMethod;
    }
}
