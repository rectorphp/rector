<?php

declare (strict_types=1);
namespace Rector\NetteUtils\Rector\StaticCall;

use RectorPrefix202506\Nette\Utils\Json;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\NetteUtils\Rector\StaticCall\UtilsJsonStaticCallNamedArgRector\UtilsJsonStaticCallNamedArgRectorTest
 */
final class UtilsJsonStaticCallNamedArgRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change `' . Json::class . '::encode()` and `decode()` to named args', [new CodeSample(<<<'CODE_SAMPLE'
use Nette\Utils\Json;

$encodedJson = Json::encode($data, true);
$decodedJson = Json::decode($json, true);

CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Utils\Json;

$encodedJson = Json::encode($data, pretty: true);
$decodedJson = Json::decode($json, forceArrays: true);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->class, 'Nette\\Utils\\Json')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (\count($node->getArgs()) < 2) {
            return null;
        }
        if (!$this->isNames($node->name, ['encode', 'decode'])) {
            return null;
        }
        // flip 2nd arg from true/false to named arg
        // check if 2nd arg is named arg already
        $secondArg = $node->getArgs()[1];
        // already set → skip
        if ($secondArg->name instanceof Identifier) {
            return null;
        }
        if ($this->isName($node->name, 'encode')) {
            $secondArg->name = new Identifier('pretty');
            return $node;
        }
        $secondArg->name = new Identifier('forceArrays');
        return $node;
    }
}
