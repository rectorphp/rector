<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor\StrncmpMatchAndRefactor;
use Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor\StrposMatchAndRefactor;
use Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor\SubstrMatchAndRefactor;

/**
 * @see https://wiki.php.net/rfc/add_str_starts_with_and_ends_with_functions
 *
 * @see https://3v4l.org/RQHB5 for weak compare
 * @see https://3v4l.org/AmLja for weak compare
 *
 * @see \Rector\Php80\Tests\Rector\Identical\StrStartsWithRector\StrStartsWithRectorTest
 */
final class StrStartsWithRector extends AbstractRector
{
    /**
     * @var StrncmpMatchAndRefactor
     */
    private $strncmpMatchAndRefactor;

    /**
     * @var StrposMatchAndRefactor
     */
    private $strposMatchAndRefactor;

    /**
     * @var SubstrMatchAndRefactor
     */
    private $substrMatchAndRefactor;

    public function __construct(
        StrncmpMatchAndRefactor $strncmpMatchAndRefactor,
        StrposMatchAndRefactor $strposMatchAndRefactor,
        SubstrMatchAndRefactor $substrMatchAndRefactor
    ) {
        $this->strncmpMatchAndRefactor = $strncmpMatchAndRefactor;
        $this->strposMatchAndRefactor = $strposMatchAndRefactor;
        $this->substrMatchAndRefactor = $substrMatchAndRefactor;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change helper functions to str_starts_with()', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $isMatch = substr($haystack, 0, strlen($needle)) === $needle;

        $isNotMatch = substr($haystack, 0, strlen($needle)) !== $needle;
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $isMatch = str_starts_with($haystack, $needle);

        $isMatch = ! str_starts_with($haystack, $needle);
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        // 1. substr
        $substrFuncCallToHaystack = $this->substrMatchAndRefactor->match($node);
        if ($substrFuncCallToHaystack !== null) {
            return $this->substrMatchAndRefactor->refactor($substrFuncCallToHaystack);
        }

        // 2. strpos
        $strposFuncCallToZero = $this->strposMatchAndRefactor->match($node);
        if ($strposFuncCallToZero !== null) {
            return $this->strposMatchAndRefactor->refactor($strposFuncCallToZero);
        }

        // 3. strcmp
        $strcmpFuncCallToHaystack = $this->strncmpMatchAndRefactor->match($node);
        if ($strcmpFuncCallToHaystack !== null) {
            return $this->strncmpMatchAndRefactor->refactor($strcmpFuncCallToHaystack);
        }

        return $node;
    }
}
