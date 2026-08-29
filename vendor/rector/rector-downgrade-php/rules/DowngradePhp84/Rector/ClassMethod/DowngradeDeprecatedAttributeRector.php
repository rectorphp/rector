<?php

declare (strict_types=1);
namespace Rector\DowngradePhp84\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/deprecated_attribute
 *
 * @see \Rector\Tests\DowngradePhp84\Rector\ClassMethod\DowngradeDeprecatedAttributeRector\DowngradeDeprecatedAttributeRectorTest
 */
final class DowngradeDeprecatedAttributeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, ValueResolver $valueResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade #[\Deprecated] attribute to @deprecated annotation', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    #[\Deprecated(message: 'use SomeOtherMethod() instead', since: '1.5')]
    public function someMethod(): void
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @deprecated 1.5 use SomeOtherMethod() instead
     */
    public function someMethod(): void
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, ClassConst::class, EnumCase::class];
    }
    /**
     * @param ClassMethod|Function_|ClassConst|EnumCase $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->attrGroups === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->attrGroups as $attrGroupKey => $attrGroup) {
            foreach ($attrGroup->attrs as $attrKey => $attribute) {
                if (!$this->isName($attribute->name, 'Deprecated')) {
                    continue;
                }
                $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
                $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@deprecated', new GenericTagValueNode($this->createTagValue($attribute))));
                unset($attrGroup->attrs[$attrKey]);
                $hasChanged = \true;
            }
            if ($attrGroup->attrs === []) {
                unset($node->attrGroups[$attrGroupKey]);
            }
        }
        if (!$hasChanged) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    private function createTagValue(Attribute $attribute): string
    {
        $message = null;
        $since = null;
        foreach ($attribute->args as $position => $arg) {
            $value = $this->valueResolver->getValue($arg->value);
            if (!is_string($value)) {
                continue;
            }
            $name = $arg instanceof Arg && $arg->name instanceof Identifier ? $arg->name->toString() : ($position === 0 ? 'message' : 'since');
            if ($name === 'since') {
                $since = $value;
            } elseif ($name === 'message') {
                $message = $value;
            }
        }
        $parts = array_filter([$since, $message], static fn(?string $part): bool => $part !== null && $part !== '');
        return implode(' ', array_map(static fn(string $part): string => (string) preg_replace('#\s+#', ' ', trim($part)), $parts));
    }
}
