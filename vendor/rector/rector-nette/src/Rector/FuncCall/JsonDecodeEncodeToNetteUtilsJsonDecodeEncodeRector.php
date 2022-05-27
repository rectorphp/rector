<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRectorTest
 */
final class JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes json_encode()/json_decode() to safer and more verbose Nette\\Utils\\Json::encode()/decode() calls', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function decodeJson(string $jsonString)
    {
        $stdClass = json_decode($jsonString);

        $array = json_decode($jsonString, true);
        $array = json_decode($jsonString, false);
    }

    public function encodeJson(array $data)
    {
        $jsonString = json_encode($data);

        $prettyJsonString = json_encode($data, JSON_PRETTY_PRINT);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function decodeJson(string $jsonString)
    {
        $stdClass = \Nette\Utils\Json::decode($jsonString);

        $array = \Nette\Utils\Json::decode($jsonString, \Nette\Utils\Json::FORCE_ARRAY);
        $array = \Nette\Utils\Json::decode($jsonString);
    }

    public function encodeJson(array $data)
    {
        $jsonString = \Nette\Utils\Json::encode($data);

        $prettyJsonString = \Nette\Utils\Json::encode($data, \Nette\Utils\Json::PRETTY);
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
        if ($this->isName($node, 'json_encode')) {
            return $this->refactorJsonEncode($node);
        }
        if ($this->isName($node, 'json_decode')) {
            return $this->refactorJsonDecode($node);
        }
        return null;
    }
    private function refactorJsonEncode(\PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\StaticCall
    {
        $args = $funcCall->args;
        if (isset($args[1])) {
            $secondArgumentValue = $args[1]->value;
            if ($this->isName($secondArgumentValue, 'JSON_PRETTY_PRINT')) {
                $classConstFetch = $this->nodeFactory->createClassConstFetch('Nette\\Utils\\Json', 'PRETTY');
                $args[1] = new \PhpParser\Node\Arg($classConstFetch);
            }
        }
        return $this->nodeFactory->createStaticCall('Nette\\Utils\\Json', 'encode', $args);
    }
    private function refactorJsonDecode(\PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\StaticCall
    {
        $args = $funcCall->args;
        if (isset($args[1])) {
            $secondArgumentValue = $args[1]->value;
            if ($this->valueResolver->isFalse($secondArgumentValue)) {
                unset($args[1]);
            } elseif ($this->valueResolver->isTrue($secondArgumentValue)) {
                $classConstFetch = $this->nodeFactory->createClassConstFetch('Nette\\Utils\\Json', 'FORCE_ARRAY');
                $args[1] = new \PhpParser\Node\Arg($classConstFetch);
            }
        }
        return $this->nodeFactory->createStaticCall('Nette\\Utils\\Json', 'decode', $args);
    }
}
