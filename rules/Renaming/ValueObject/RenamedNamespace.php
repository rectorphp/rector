<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Renaming\ValueObject;

final class RenamedNamespace
{
    /**
     * @readonly
     * @var string
     */
    private $currentName;
    /**
     * @readonly
     * @var string
     */
    private $oldNamespace;
    /**
     * @readonly
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
        if ($this->newNamespace === $this->currentName) {
            return $this->currentName;
        }
        return \str_replace($this->oldNamespace, $this->newNamespace, $this->currentName);
    }
    public function getNewNamespace() : string
    {
        return $this->newNamespace;
    }
}
