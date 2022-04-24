<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\NodeFactory\CheckRequirementsClassMethodFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/nette/application/commit/a70c7256b645a2bee0b0c2c735020d7043a14558#diff-549e1fc650c1fc7e138900598027656a50d12b031605f8a63a38bd69a3985fafR1324
 */
final class MoveFinalGetUserToCheckRequirementsClassMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Nette\NodeFactory\CheckRequirementsClassMethodFactory
     */
    private $checkRequirementsClassMethodFactory;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    public function __construct(\Rector\Nette\NodeFactory\CheckRequirementsClassMethodFactory $checkRequirementsClassMethodFactory, \Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator)
    {
        $this->checkRequirementsClassMethodFactory = $checkRequirementsClassMethodFactory;
        $this->classInsertManipulator = $classInsertManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Presenter method getUser() is now final, move logic to checkRequirements()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Nette\Application\UI\Presenter;

class SomeControl extends Presenter
{
    public function getUser()
    {
        $user = parent::getUser();
        $user->getStorage()->setNamespace('admin_session');
        return $user;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Application\UI\Presenter;

class SomeControl extends Presenter
{
    public function checkRequirements()
    {
        $user = $this->getUser();
        $user->getStorage()->setNamespace('admin_session');

        parent::checkRequirements();
    }
}
CODE_SAMPLE
)]);
    }
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
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Presenter'))) {
            return null;
        }
        $getUserClassMethod = $node->getMethod('getUser');
        if (!$getUserClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        $checkRequirementsClassMethod = $node->getMethod('checkRequirements');
        if (!$checkRequirementsClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $checkRequirementsClassMethod = $this->checkRequirementsClassMethodFactory->create((array) $getUserClassMethod->stmts);
            $this->classInsertManipulator->addAsFirstMethod($node, $checkRequirementsClassMethod);
        } else {
            throw new \Rector\Core\Exception\NotImplementedYetException();
        }
        $this->removeNode($getUserClassMethod);
        return $node;
    }
}
