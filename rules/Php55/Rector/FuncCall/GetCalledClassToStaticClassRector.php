<?php

declare (strict_types=1);
namespace Rector\Php55\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/ChangeLog-5.php#5.5.0
 * @changelog https://3v4l.org/dJgXd
 * @see \Rector\Tests\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector\GetCalledClassToStaticClassRectorTest
 */
final class GetCalledClassToStaticClassRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(\Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer)
    {
        $this->classAnalyzer = $classAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change get_called_class() to static::class on non-final class', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
   public function callOnMe()
   {
       var_dump(get_called_class());
   }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
   public function callOnMe()
   {
       var_dump(static::class);
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'get_called_class')) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return $this->nodeFactory->createClassConstFetch(\Rector\Core\Enum\ObjectReference::STATIC, 'class');
        }
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return null;
        }
        if (!$class->isFinal()) {
            return $this->nodeFactory->createClassConstFetch(\Rector\Core\Enum\ObjectReference::STATIC, 'class');
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::CLASSNAME_CONSTANT;
    }
}
