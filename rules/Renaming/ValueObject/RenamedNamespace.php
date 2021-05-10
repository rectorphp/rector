<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

final class RenamedNamespace
{
    /**
     * @var string
     */
    private $currentName;
    /**
     * @var string
     */
    private $oldNamespace;
    /**
     * @var string
     */
    private $newNamespace;
    public function __construct(string $currentName, string $oldNamespace, string $newNamespace)
    {
        $this->currentName = $currentName;
        $this->oldNamespace = $oldNamespace;
        $this->newNamespace = $newNamespace;
    }
    public function getNameInNewNamespace() : string
    {
        return \str_replace($this->oldNamespace, $this->newNamespace, $this->currentName);
    }
}
