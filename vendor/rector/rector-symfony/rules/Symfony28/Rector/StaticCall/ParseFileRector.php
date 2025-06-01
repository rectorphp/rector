<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony28\Rector\StaticCall;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\Rector\AbstractRector;
use Rector\Util\StringUtils;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony28\Rector\StaticCall\ParseFileRector\ParseFileRectorTest
 */
final class ParseFileRector extends AbstractRector
{
    /**
     * @readonly
     */
    private BetterStandardPrinter $betterStandardPrinter;
    /**
     * @var string
     * @changelog https://regex101.com/r/ZaY42i/1
     */
    private const YAML_SUFFIX_IN_QUOTE_REGEX = '#\\.(yml|yaml)(\'|\\")$#';
    /**
     * @var string
     * @changelog https://regex101.com/r/YHA05g/1
     */
    private const FILE_SUFFIX_REGEX = '#File$#';
    /**
     * @var string
     * @changelog https://regex101.com/r/JmNhZj/1
     */
    private const YAML_SUFFIX_REGEX = '#\\.(yml|yaml)$#';
    public function __construct(BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces deprecated Yaml::parse() of file argument with file contents', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Yaml\Yaml;

$parsedFile = Yaml::parse('someFile.yml');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Yaml\Yaml;

$parsedFile = Yaml::parse(file_get_contents('someFile.yml'));
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * Process Node of matched type
     *
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->name, 'parse')) {
            return null;
        }
        if (!$this->isObjectType($node->class, new ObjectType('Symfony\\Component\\Yaml\\Yaml'))) {
            return null;
        }
        if (!$this->isArgumentYamlFile($node)) {
            return null;
        }
        $funcCall = $this->nodeFactory->createFuncCall('file_get_contents', [$node->args[0]]);
        $node->args[0] = new Arg($funcCall);
        return $node;
    }
    private function isArgumentYamlFile(StaticCall $staticCall) : bool
    {
        $firstArg = $staticCall->args[0];
        if (!$firstArg instanceof Arg) {
            return \false;
        }
        $possibleFileNode = $firstArg->value;
        $possibleFileNodeAsString = $this->betterStandardPrinter->print($possibleFileNode);
        // is yml/yaml file
        if (StringUtils::isMatch($possibleFileNodeAsString, self::YAML_SUFFIX_IN_QUOTE_REGEX)) {
            return \true;
        }
        // is probably a file variable
        if (StringUtils::isMatch($possibleFileNodeAsString, self::FILE_SUFFIX_REGEX)) {
            return \true;
        }
        // try to detect current value
        $nodeScope = $possibleFileNode->getAttribute(AttributeKey::SCOPE);
        if (!$nodeScope instanceof Scope) {
            return \false;
        }
        $nodeType = $nodeScope->getType($possibleFileNode);
        if (!$nodeType instanceof ConstantStringType) {
            return \false;
        }
        return (bool) Strings::match($nodeType->getValue(), self::YAML_SUFFIX_REGEX);
    }
}
