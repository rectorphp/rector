<?php

declare(strict_types=1);

namespace Rector\JMS\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSServiceValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/rectorphp/rector/issues/2864
 *
 * @see \Rector\JMS\Tests\Rector\Class_\RemoveJmsInjectServiceAnnotationRector\RemoveJmsInjectServiceAnnotationRectorTest
 */
final class RemoveJmsInjectServiceAnnotationRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes JMS\DiExtraBundle\Annotation\Services annotation', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("email.web.services.subscribe_token", public=true)
 */
class SomeClass
{
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
use JMS\DiExtraBundle\Annotation as DI;

class SomeClass
{
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, ClassMethod::class];
    }

    /**
     * @param Class_|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo->hasByType(JMSServiceValueNode::class)) {
            return null;
        }

        $phpDocInfo->removeByType(JMSServiceValueNode::class);

        return $node;
    }
}
