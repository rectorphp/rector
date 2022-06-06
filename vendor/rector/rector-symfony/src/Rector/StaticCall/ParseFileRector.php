<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Rector\StaticCall;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantStringType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Contract\PhpParser\NodePrinterInterface;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\Util\StringUtils;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\StaticCall\ParseFileRector\ParseFileRectorTest
 */
final class ParseFileRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/ZaY42i/1
     */
    private const YAML_SUFFIX_IN_QUOTE_REGEX = '#\\.(yml|yaml)(\'|\\")$#';
    /**
     * @var string
     * @see https://regex101.com/r/YHA05g/1
     */
    private const FILE_SUFFIX_REGEX = '#File$#';
    /**
     * @var string
     * @see https://regex101.com/r/JmNhZj/1
     */
    private const YAML_SUFFIX_REGEX = '#\\.(yml|yaml)$#';
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    public function __construct(NodePrinterInterface $nodePrinter)
    {
        $this->nodePrinter = $nodePrinter;
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
        $possibleFileNodeAsString = $this->nodePrinter->print($possibleFileNode);
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
