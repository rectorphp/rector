<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\JMS;

use JMS\DiExtraBundle\Annotation\Inject;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class JMSInjectTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@DI\Inject';

    /**
     * @var string
     */
    public const CLASS_NAME = Inject::class;

    /**
     * @var string|null
     */
    private $serviceName;

    /**
     * @var bool|null
     */
    private $required;

    /**
     * @var bool
     */
    private $strict = false;

    public function __construct(?string $serviceName, ?bool $required, bool $strict)
    {
        $this->serviceName = $serviceName;
        $this->required = $required;
        $this->strict = $strict;
    }

    public function __toString(): string
    {
        $itemContents = [];

        if ($this->serviceName) {
            $itemContents[] = '"' . $this->serviceName . '"';
        }

        if ($this->required !== null) {
            $itemContents[] = sprintf('required=%s', $this->required ? 'true' : 'false');
        }

        // skip default
        if (! $this->strict) {
            $itemContents[] = sprintf('strict=%s', $this->strict ? 'true' : 'false');
        }

        if ($itemContents === []) {
            return '';
        }

        return $this->printContentItems($itemContents);
    }

    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }
}
