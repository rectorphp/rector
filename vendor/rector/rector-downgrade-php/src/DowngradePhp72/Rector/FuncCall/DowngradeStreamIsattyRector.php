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
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/polyfill/commit/cc2bf55accd32b989348e2039e8c91cde46aebed
 *
 * @see \Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector\DowngradeStreamIsattyRectorTest
 */
final class DowngradeStreamIsattyRector extends AbstractScopeAwareRector
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
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(InlineCodeParser $inlineCodeParser, FunctionExistsFunCallAnalyzer $functionExistsFunCallAnalyzer, VariableNaming $variableNaming, NodesToAddCollector $nodesToAddCollector)
    {
        $this->inlineCodeParser = $inlineCodeParser;
        $this->functionExistsFunCallAnalyzer = $functionExistsFunCallAnalyzer;
        $this->variableNaming = $variableNaming;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade stream_isatty() function', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if (!$this->isName($node, 'stream_isatty')) {
            return null;
        }
        if ($this->functionExistsFunCallAnalyzer->detect($node, 'stream_isatty')) {
            return null;
        }
        $function = $this->createClosure();
        $variable = new Variable($this->variableNaming->createCountedValueName('streamIsatty', $scope));
        $assign = new Assign($variable, $function);
        $this->nodesToAddCollector->addNodeBeforeNode($assign, $node);
        return new FuncCall($variable, $node->args);
    }
    private function createClosure() : Closure
    {
        if ($this->cachedClosure instanceof Closure) {
            return clone $this->cachedClosure;
        }
        $stmts = $this->inlineCodeParser->parse(__DIR__ . '/../../snippet/isatty_closure.php.inc');
        /** @var Expression $expression */
        $expression = $stmts[0];
        $expr = $expression->expr;
        if (!$expr instanceof Closure) {
            throw new ShouldNotHappenException();
        }
        $this->cachedClosure = $expr;
        return $expr;
    }
}
