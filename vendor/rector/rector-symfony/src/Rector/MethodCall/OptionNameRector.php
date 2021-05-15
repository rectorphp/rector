<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer;
use Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\OptionNameRector\OptionNameRectorTest
 */
final class OptionNameRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_OPTION = ['precision' => 'scale', 'virtual' => 'inherit_data'];
    /**
     * @var \Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer
     */
    private $formAddMethodCallAnalyzer;
    /**
     * @var \Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher
     */
    private $formOptionsArrayMatcher;
    public function __construct(\Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer $formAddMethodCallAnalyzer, \Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher $formOptionsArrayMatcher)
    {
        $this->formAddMethodCallAnalyzer = $formAddMethodCallAnalyzer;
        $this->formOptionsArrayMatcher = $formOptionsArrayMatcher;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns old option names to new ones in FormTypes in Form in Symfony', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$builder = new FormBuilder;
$builder->add("...", ["precision" => "...", "virtual" => "..."];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$builder = new FormBuilder;
$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->formAddMethodCallAnalyzer->isMatching($node)) {
            return null;
        }
        $optionsArray = $this->formOptionsArrayMatcher->match($node);
        if (!$optionsArray instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        foreach ($optionsArray->items as $arrayItemNode) {
            if ($arrayItemNode === null) {
                continue;
            }
            if (!$arrayItemNode->key instanceof \PhpParser\Node\Scalar\String_) {
                continue;
            }
            $this->processStringKey($arrayItemNode->key);
        }
        return $node;
    }
    private function processStringKey(\PhpParser\Node\Scalar\String_ $string) : void
    {
        $currentOptionName = $string->value;
        $string->value = self::OLD_TO_NEW_OPTION[$currentOptionName] ?? $string->value;
    }
}
