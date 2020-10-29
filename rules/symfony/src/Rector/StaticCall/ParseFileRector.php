<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\StaticCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\Constant\ConstantStringType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Symfony\Tests\Rector\StaticCall\ParseFileRector\ParseFileRectorTest
 */
final class ParseFileRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/ZaY42i/1
     */
    private const YAML_SUFFIX_IN_QUOTE_REGEX = '#\.(yml|yaml)(\'|")$#';

    /**
     * @var string
     * @see https://regex101.com/r/YHA05g/1
     */
    private const FILE_SUFFIX_REGEX = '#\File$#';

    /**
     * @var string
     * @see https://regex101.com/r/JmNhZj/1
     */
    private const YAML_SUFFIX_REGEX = '#\.(yml|yaml)$#';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('session > use_strict_mode is true by default and can be removed', [
            new CodeSample('session > use_strict_mode: true', 'session:'),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * Process Node of matched type
     *
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->name, 'parse')) {
            return null;
        }

        if (! $this->isObjectType($node->class, 'Symfony\Component\Yaml\Yaml')) {
            return null;
        }

        if (! $this->isArgumentYamlFile($node)) {
            return null;
        }

        $funcCall = $this->createFuncCall('file_get_contents', [$node->args[0]]);
        $node->args[0] = new Arg($funcCall);

        return $node;
    }

    private function isArgumentYamlFile(StaticCall $staticCall): bool
    {
        $possibleFileNode = $staticCall->args[0]->value;

        $possibleFileNodeAsString = $this->print($possibleFileNode);

        // is yml/yaml file
        if (Strings::match($possibleFileNodeAsString, self::YAML_SUFFIX_IN_QUOTE_REGEX)) {
            return true;
        }

        // is probably a file variable
        if (Strings::match($possibleFileNodeAsString, self::FILE_SUFFIX_REGEX)) {
            return true;
        }

        // try to detect current value
        $nodeScope = $possibleFileNode->getAttribute(AttributeKey::SCOPE);
        if ($nodeScope === null) {
            throw new ShouldNotHappenException();
        }

        $nodeType = $nodeScope->getType($possibleFileNode);
        return $nodeType instanceof ConstantStringType && Strings::match(
            $nodeType->getValue(),
            self::YAML_SUFFIX_REGEX
        );
    }
}
