<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\NullsafeMethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Application\File;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector\DowngradeNullsafeToTernaryOperatorRectorTest
 */
final class DowngradeNullsafeToTernaryOperatorRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @var int
     */
    private $counter = 0;
    /**
     * @var string|null
     */
    private $previousFileName;
    /**
     * @var string|null
     */
    private $currentFileName;
    public function __construct(CurrentFileProvider $currentFileProvider)
    {
        $this->currentFileProvider = $currentFileProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change nullsafe operator to ternary operator rector', [new CodeSample(<<<'CODE_SAMPLE'
$dateAsString = $booking->getStartDate()?->asDateTimeString();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$dateAsString = ($bookingGetStartDate = $booking->getStartDate()) ? $bookingGetStartDate->asDateTimeString() : null;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [NullsafeMethodCall::class, NullsafePropertyFetch::class];
    }
    /**
     * @param NullsafeMethodCall|NullsafePropertyFetch $node
     */
    public function refactor(Node $node) : ?Ternary
    {
        if ($this->previousFileName === null) {
            $previousFile = $this->currentFileProvider->getFile();
            if (!$previousFile instanceof File) {
                return null;
            }
            $this->previousFileName = $previousFile->getFilePath();
        }
        $currentFile = $this->currentFileProvider->getFile();
        if (!$currentFile instanceof File) {
            return null;
        }
        $this->currentFileName = $currentFile->getFilePath();
        $nullsafeVariable = $this->createNullsafeVariable();
        $methodCallOrPropertyFetch = $node instanceof NullsafeMethodCall ? new MethodCall($nullsafeVariable, $node->name, $node->getArgs()) : new PropertyFetch($nullsafeVariable, $node->name);
        $assign = new Assign($nullsafeVariable, $node->var);
        return new Ternary($assign, $methodCallOrPropertyFetch, $this->nodeFactory->createNull());
    }
    private function createNullsafeVariable() : Variable
    {
        if ($this->previousFileName !== $this->currentFileName) {
            $this->counter = 0;
            $this->previousFileName = $this->currentFileName;
        }
        $nullsafeVariableName = 'nullsafeVariable' . ++$this->counter;
        return new Variable($nullsafeVariableName);
    }
}
