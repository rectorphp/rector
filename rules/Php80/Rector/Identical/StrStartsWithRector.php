<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Rector\Php80\Contract\StrStartWithMatchAndRefactorInterface;
use Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor\StrncmpMatchAndRefactor;
use Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor\StrposMatchAndRefactor;
use Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor\SubstrMatchAndRefactor;
use Rector\Php80\ValueObject\StrStartsWith;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\ValueObject\PolyfillPackage;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Rector\VersionBonding\Contract\RelatedPolyfillInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\StrStartsWithRectorTest
 */
final class StrStartsWithRector extends AbstractRector implements MinPhpVersionInterface, RelatedPolyfillInterface
{
    /**
     * @var StrStartWithMatchAndRefactorInterface[]
     */
    private array $strStartWithMatchAndRefactors = [];
    public function __construct(StrncmpMatchAndRefactor $strncmpMatchAndRefactor, SubstrMatchAndRefactor $substrMatchAndRefactor, StrposMatchAndRefactor $strposMatchAndRefactor)
    {
        $this->strStartWithMatchAndRefactors = [$strncmpMatchAndRefactor, $substrMatchAndRefactor, $strposMatchAndRefactor];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::STR_STARTS_WITH;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change helper functions to str_starts_with()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Identical::class, NotIdentical::class, Equal::class, NotEqual::class];
    }
    /**
     * @param Identical|NotIdentical|Equal|NotEqual $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($this->strStartWithMatchAndRefactors as $strStartWithMatchAndRefactor) {
            $strStartsWithValueObject = $strStartWithMatchAndRefactor->match($node);
            if (!$strStartsWithValueObject instanceof StrStartsWith) {
                continue;
            }
            return $strStartWithMatchAndRefactor->refactorStrStartsWith($strStartsWithValueObject);
        }
        return null;
    }
    public function providePolyfillPackage() : string
    {
        return PolyfillPackage::PHP_80;
    }
}
