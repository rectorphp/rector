<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class EntityTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string|null
     */
    private $repositoryClass;

    /**
     * @var bool|null
     */
    private $readOnly;

    public function __construct(
        ?string $repositoryClass = null,
        ?bool $readOnly = null,
        ?string $originalContent = null
    ) {
        $this->repositoryClass = $repositoryClass;
        $this->readOnly = $readOnly;

        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->repositoryClass !== null) {
            $contentItems['repositoryClass'] = sprintf('repositoryClass="%s"', $this->repositoryClass);
        }

        if ($this->readOnly !== null) {
            $contentItems['readOnly'] = sprintf('readOnly=%s', $this->readOnly ? 'true' : 'false');
        }

        return $this->printContentItems($contentItems);
    }

    public function removeRepositoryClass(): void
    {
        $this->repositoryClass = null;
    }

    public function getShortName(): string
    {
        return '@ORM\Entity';
    }
}
