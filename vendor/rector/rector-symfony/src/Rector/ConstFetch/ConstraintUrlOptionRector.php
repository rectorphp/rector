<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Rector\ConstFetch;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\New_;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#validator
 *
 * @see \Rector\Symfony\Tests\Rector\ConstFetch\ConstraintUrlOptionRector\ConstraintUrlOptionRectorTest
 */
final class ConstraintUrlOptionRector extends AbstractRector
{
    /**
     * @var string
     */
    private const URL_CONSTRAINT_CLASS = 'Symfony\\Component\\Validator\\Constraints\\Url';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.', [new CodeSample('$constraint = new Url(["checkDNS" => true]);', '$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ConstFetch::class];
    }
    /**
     * @param ConstFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->valueResolver->isTrue($node)) {
            return null;
        }
        if (!$this->isInsideNewUrl($node)) {
            return null;
        }
        $prevNode = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (!$prevNode instanceof String_) {
            return null;
        }
        if ($prevNode->value !== 'checkDNS') {
            return null;
        }
        return $this->nodeFactory->createClassConstFetch(self::URL_CONSTRAINT_CLASS, 'CHECK_DNS_TYPE_ANY');
    }
    private function isInsideNewUrl(ConstFetch $constFetch) : bool
    {
        $new = $this->betterNodeFinder->findParentType($constFetch, New_::class);
        if (!$new instanceof New_) {
            return \false;
        }
        $newType = $this->getType($new);
        if (!$newType instanceof TypeWithClassName) {
            return \false;
        }
        return $newType->getClassName() === self::URL_CONSTRAINT_CLASS;
    }
}
