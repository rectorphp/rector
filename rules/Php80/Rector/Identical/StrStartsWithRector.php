<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\Contract\StrStartWithMatchAndRefactorInterface;
use Rector\Php80\ValueObject\StrStartsWith;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/add_str_starts_with_and_ends_with_functions
 *
 * @changelog https://3v4l.org/RQHB5 for weak compare
 * @changelog https://3v4l.org/AmLja for weak compare
 *
 * @see \Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\StrStartsWithRectorTest
 */
final class StrStartsWithRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var StrStartWithMatchAndRefactorInterface[]
     * @readonly
     */
    private $strStartWithMatchAndRefactors;
    /**
     * @param StrStartWithMatchAndRefactorInterface[] $strStartWithMatchAndRefactors
     */
    public function __construct(array $strStartWithMatchAndRefactors)
    {
        $this->strStartWithMatchAndRefactors = $strStartWithMatchAndRefactors;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::STR_STARTS_WITH;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change helper functions to str_starts_with()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = substr($haystack, 0, strlen($needle)) === $needle;

        $isNotMatch = substr($haystack, 0, strlen($needle)) !== $needle;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = str_starts_with($haystack, $needle);

        $isNotMatch = ! str_starts_with($haystack, $needle);
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
        return [\PhpParser\Node\Expr\BinaryOp\Identical::class, \PhpParser\Node\Expr\BinaryOp\NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->strStartWithMatchAndRefactors as $strStartWithMatchAndRefactor) {
            $strStartsWithValueObject = $strStartWithMatchAndRefactor->match($node);
            if (!$strStartsWithValueObject instanceof \Rector\Php80\ValueObject\StrStartsWith) {
                continue;
            }
            return $strStartWithMatchAndRefactor->refactorStrStartsWith($strStartsWithValueObject);
        }
        return null;
    }
}
