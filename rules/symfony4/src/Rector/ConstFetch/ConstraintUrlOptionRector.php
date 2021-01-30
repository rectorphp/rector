<?php

declare(strict_types=1);

namespace Rector\Symfony4\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#validator
 * @see \Rector\Symfony4\Tests\Rector\ConstFetch\ConstraintUrlOptionRector\ConstraintUrlOptionRectorTest
 */
final class ConstraintUrlOptionRector extends AbstractRector
{
    /**
     * @var string
     */
    private const URL_CONSTRAINT_CLASS = 'Symfony\Component\Validator\Constraints\Url';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.',
            [
                new CodeSample(
                    '$constraint = new Url(["checkDNS" => true]);',
                    '$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);'
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ConstFetch::class];
    }

    /**
     * @param ConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->valueResolver->isTrue($node)) {
            return null;
        }

        $prevNode = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (! $prevNode instanceof String_) {
            return null;
        }

        if ($prevNode->value !== 'checkDNS') {
            return null;
        }

        return $this->nodeFactory->createClassConstFetch(self::URL_CONSTRAINT_CLASS, 'CHECK_DNS_TYPE_ANY');
    }
}
