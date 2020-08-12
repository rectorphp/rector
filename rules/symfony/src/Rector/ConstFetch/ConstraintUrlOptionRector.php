<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#validator
 * @see \Rector\Symfony\Tests\Rector\ConstFetch\ConstraintUrlOptionRector\ConstraintUrlOptionRectorTest
 */
final class ConstraintUrlOptionRector extends AbstractRector
{
    /**
     * @var string
     */
    private const URL_CONSTRAINT_CLASS = 'Symfony\Component\Validator\Constraints\Url';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.', [
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
        if (! $this->isTrue($node)) {
            return null;
        }

        $prevNode = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (! $prevNode instanceof String_) {
            return null;
        }

        if ($prevNode->value !== 'checkDNS') {
            return null;
        }

        return $this->createClassConstFetch(self::URL_CONSTRAINT_CLASS, 'CHECK_DNS_TYPE_ANY');
    }
}
