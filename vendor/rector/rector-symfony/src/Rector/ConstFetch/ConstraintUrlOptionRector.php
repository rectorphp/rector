<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#validator
 *
 * @see \Rector\Symfony\Tests\Rector\ConstFetch\ConstraintUrlOptionRector\ConstraintUrlOptionRectorTest
 */
final class ConstraintUrlOptionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const URL_CONSTRAINT_CLASS = 'Symfony\\Component\\Validator\\Constraints\\Url';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$constraint = new Url(["checkDNS" => true]);', '$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\ConstFetch::class];
    }
    /**
     * @param ConstFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->valueResolver->isTrue($node)) {
            return null;
        }
        if (!$this->isInsideNewUrl($node)) {
            return null;
        }
        $prevNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
        if (!$prevNode instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        if ($prevNode->value !== 'checkDNS') {
            return null;
        }
        return $this->nodeFactory->createClassConstFetch(self::URL_CONSTRAINT_CLASS, 'CHECK_DNS_TYPE_ANY');
    }
    private function isInsideNewUrl(\PhpParser\Node\Expr\ConstFetch $constFetch) : bool
    {
        $new = $this->betterNodeFinder->findParentType($constFetch, \PhpParser\Node\Expr\New_::class);
        if (!$new instanceof \PhpParser\Node\Expr\New_) {
            return \false;
        }
        $newType = $this->getType($new);
        if (!$newType instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        if ($newType->getClassName() !== self::URL_CONSTRAINT_CLASS) {
            return \false;
        }
        return \true;
    }
}
