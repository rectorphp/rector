<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\JMS;

use JMS\DiExtraBundle\Annotation\Service;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class JMSServiceValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var string
     */
    public const CLASS_NAME = Service::class;

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
        return '@DI\Service';
    }
}
