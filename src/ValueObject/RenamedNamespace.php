<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

final class RenamedNamespace
{
    /**
     * @var string
     */
    private $oldNamespace;

    /**
     * @var string
     */
    private $newNamespace;

    /**
     * @var string
     */
    private $currentName;

    public function __construct(string $currentNamespaceName, string $oldNamespace, string $newNamespace)
    {
        $this->currentName = $currentNamespaceName;
        $this->oldNamespace = $oldNamespace;
        $this->newNamespace = $newNamespace;
    }

    public function getNameInNewNamespace(): string
    {
        return str_replace($this->oldNamespace, $this->newNamespace, $this->currentName);
    }
}
