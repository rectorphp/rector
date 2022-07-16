<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

use Rector\Core\Validation\RectorAssert;
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
        RectorAssert::namespaceName($currentName);
        RectorAssert::namespaceName($oldNamespace);
        RectorAssert::namespaceName($newNamespace);
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
