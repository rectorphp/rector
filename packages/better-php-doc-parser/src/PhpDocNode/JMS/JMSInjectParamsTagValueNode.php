<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\JMS;

use JMS\DiExtraBundle\Annotation\InjectParams;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class JMSInjectParamsTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var string
     */
    public const CLASS_NAME = InjectParams::class;

    public function __construct(string $originalContent)
    {
        $this->originalContent = $originalContent;
    }

    public function __toString(): string
    {
        return $this->originalContent;
    }

    public function getShortName(): string
    {
        return '@DI\InjectParams';
    }
}
