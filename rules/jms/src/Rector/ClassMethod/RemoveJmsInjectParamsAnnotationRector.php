<?php

declare(strict_types=1);

namespace Rector\JMS\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSInjectParamsTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/rectorphp/rector/issues/2864
 *
 * @see \Rector\JMS\Tests\Rector\ClassMethod\RemoveJmsInjectParamsAnnotationRector\RemoveJmsInjectParamsAnnotationRectorTest
 */
final class RemoveJmsInjectParamsAnnotationRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes JMS\DiExtraBundle\Annotation\InjectParams annotation', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use JMS\DiExtraBundle\Annotation as DI;

class SomeClass
{
    /**
     * @DI\InjectParams({
     *     "subscribeService" = @DI\Inject("app.email.service.subscribe"),
     *     "ipService" = @DI\Inject("app.util.service.ip")
     * })
     */
    public function __construct()
    {
    }
}

CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
use JMS\DiExtraBundle\Annotation as DI;

class SomeClass
{
    public function __construct()
    {
    }
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        $phpDocInfo->removeByType(JMSInjectParamsTagValueNode::class);

        return $node;
    }
}
