<?php

declare (strict_types=1);
namespace RectorPrefix20210514\Symplify\SetConfigResolver\Exception;

use Exception;
final class SetNotFoundException extends \Exception
{
    /**
     * @var string
     */
    private $setName;
    /**
     * @var string[]
     */
    private $availableSetNames = [];
    /**
     * @param string[] $availableSetNames
     */
    public function __construct(string $message, string $setName, array $availableSetNames)
    {
        $this->setName = $setName;
        $this->availableSetNames = $availableSetNames;
        parent::__construct($message);
    }
    public function getSetName() : string
    {
        return $this->setName;
    }
    /**
     * @return string[]
     */
    public function getAvailableSetNames() : array
    {
        return $this->availableSetNames;
    }
}
