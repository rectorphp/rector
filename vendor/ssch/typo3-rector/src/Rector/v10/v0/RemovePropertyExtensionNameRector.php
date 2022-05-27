<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-87627-RemovePropertyExtensionNameOfAbstractController.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v0\RemovePropertyExtensionNameRector\RemovePropertyExtensionNameRectorTest
 */
final class RemovePropertyExtensionNameRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [PropertyFetch::class];
    }
    /**
     * @param PropertyFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isName($node, 'extensionName')) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createPropertyFetch($node->var, 'request'), 'getControllerExtensionName');
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use method getControllerExtensionName from $request property instead of removed property $extensionName', [new CodeSample(<<<'CODE_SAMPLE'
class MyCommandController extends CommandController
{
    public function myMethod()
    {
        if($this->extensionName === 'whatever') {

        }

        $extensionName = $this->extensionName;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class MyCommandController extends CommandController
{
    public function myMethod()
    {
        if($this->request->getControllerExtensionName() === 'whatever') {

        }

        $extensionName = $this->request->getControllerExtensionName();
    }
}
CODE_SAMPLE
)]);
    }
    private function shouldSkip(PropertyFetch $propertyFetch) : bool
    {
        if ($this->isObjectType($propertyFetch->var, new ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\AbstractController'))) {
            return \false;
        }
        return !$this->isObjectType($propertyFetch->var, new ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController'));
    }
}
