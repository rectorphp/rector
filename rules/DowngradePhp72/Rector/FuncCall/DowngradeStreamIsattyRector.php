<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Parser\InlineCodeParser;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\DowngradePhp72\NodeAnalyzer\FunctionExistsFunCallAnalyzer;
use Rector\Naming\Naming\VariableNaming;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/polyfill/commit/cc2bf55accd32b989348e2039e8c91cde46aebed
 *
 * @see \Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector\DowngradeStreamIsattyRectorTest
 */
final class DowngradeStreamIsattyRector extends \Rector\Core\Rector\AbstractScopeAwareRector
{
    /**
     * @var \PhpParser\Node\Expr\Closure|null
     */
    private $cachedClosure;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\InlineCodeParser
     */
    private $inlineCodeParser;
    /**
     * @readonly
     * @var \Rector\DowngradePhp72\NodeAnalyzer\FunctionExistsFunCallAnalyzer
     */
    private $functionExistsFunCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(\Rector\Core\PhpParser\Parser\InlineCodeParser $inlineCodeParser, \Rector\DowngradePhp72\NodeAnalyzer\FunctionExistsFunCallAnalyzer $functionExistsFunCallAnalyzer, \Rector\Naming\Naming\VariableNaming $variableNaming)
    {
        $this->inlineCodeParser = $inlineCodeParser;
        $this->functionExistsFunCallAnalyzer = $functionExistsFunCallAnalyzer;
        $this->variableNaming = $variableNaming;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade stream_isatty() function', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($stream)
    {
        $isStream = stream_isatty($stream);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($stream)
    {
        $streamIsatty = function ($stream) {
            if (\function_exists('stream_isatty')) {
                return stream_isatty($stream);
            }

            if (!\is_resource($stream)) {
                trigger_error('stream_isatty() expects parameter 1 to be resource, '.\gettype($stream).' given', \E_USER_WARNING);

                return false;
            }

            if ('\\' === \DIRECTORY_SEPARATOR) {
                $stat = @fstat($stream);
                // Check if formatted mode is S_IFCHR
                return $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
            }

            return \function_exists('posix_isatty') && @posix_isatty($stream);
        };
        $isStream = $streamIsatty($stream);
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
    public function refactorWithScope(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'stream_isatty')) {
            return null;
        }
        if ($this->functionExistsFunCallAnalyzer->detect($node, 'stream_isatty')) {
            return null;
        }
        $function = $this->createClosure();
        $variable = new \PhpParser\Node\Expr\Variable($this->variableNaming->createCountedValueName('streamIsatty', $scope));
        $assign = new \PhpParser\Node\Expr\Assign($variable, $function);
        $this->nodesToAddCollector->addNodeBeforeNode($assign, $node, $this->file->getSmartFileInfo());
        return new \PhpParser\Node\Expr\FuncCall($variable, $node->args);
    }
    private function createClosure() : \PhpParser\Node\Expr\Closure
    {
        if ($this->cachedClosure instanceof \PhpParser\Node\Expr\Closure) {
            return clone $this->cachedClosure;
        }
        $stmts = $this->inlineCodeParser->parse(__DIR__ . '/../../snippet/isatty_closure.php.inc');
        /** @var Expression $expression */
        $expression = $stmts[0];
        $expr = $expression->expr;
        if (!$expr instanceof \PhpParser\Node\Expr\Closure) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $this->cachedClosure = $expr;
        return $expr;
    }
}
