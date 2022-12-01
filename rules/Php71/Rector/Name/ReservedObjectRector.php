<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\Name;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202212\Webmozart\Assert\Assert;
/**
 * @changelog https://wiki.php.net/rfc/object-typehint https://github.com/cebe/yii2/commit/9548a212ecf6e50fcdb0e5ba6daad88019cfc544
 *
 * @see \Rector\Tests\Php71\Rector\Name\ReservedObjectRector\ReservedObjectRectorTest
 */
final class ReservedObjectRector extends AbstractRector implements ConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @var array<string, string>
     */
    private $reservedKeywordsToReplacements = [];
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::RESERVED_OBJECT_KEYWORD;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes reserved "Object" name to "<Smart>Object" where <Smart> can be configured', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class Object
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SmartObject
{
}
CODE_SAMPLE
, ['ReservedObject' => 'SmartObject', 'Object' => 'AnotherSmartObject'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Identifier::class, Name::class];
    }
    /**
     * @param Identifier|Name $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Identifier) {
            return $this->processIdentifier($node);
        }
        return $this->processName($node);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString(\array_keys($configuration));
        Assert::allString($configuration);
        $this->reservedKeywordsToReplacements = $configuration;
    }
    private function processIdentifier(Identifier $identifier) : Identifier
    {
        foreach ($this->reservedKeywordsToReplacements as $reservedKeyword => $replacement) {
            if (!$this->isName($identifier, $reservedKeyword)) {
                continue;
            }
            $identifier->name = $replacement;
            return $identifier;
        }
        return $identifier;
    }
    private function processName(Name $name) : Name
    {
        // we look for "extends <Name>"
        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
        // "Object" can part of namespace name
        if ($parentNode instanceof Namespace_) {
            return $name;
        }
        // process lass part
        foreach ($this->reservedKeywordsToReplacements as $reservedKeyword => $replacement) {
            if (\strtolower($name->getLast()) === \strtolower($reservedKeyword)) {
                $name->parts[\count($name->parts) - 1] = $replacement;
                // invoke override
                $name->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            }
        }
        return $name;
    }
}
