<?php

declare(strict_types=1);

namespace Rector\Symfony\PhpDocParser\Ast\PhpDoc;

use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

abstract class AbstractConstraintTagValueNode extends AbstractTagValueNode
{
    /**
     * @var mixed[]
     */
    protected $groups = [];

    /**
     * @var string|null
     */
    private $message;

    /**
     * @param mixed[] $groups
     */
    public function __construct(array $groups, ?string $message = null)
    {
        $this->groups = $groups;
        $this->message = $message;
    }

    protected function appendGroups(array $contentItems): array
    {
        if ($this->groups === []) {
            return $contentItems;
        }

        if (count($this->groups) === 1) {
            if ($this->groups !== ['Default']) {
                $contentItems['groups'] = sprintf('groups=%s', $this->groups[0]);
            }
        } else {
            $contentItems['groups'] = sprintf('groups=%s', $this->printArrayItem($this->groups));
        }

        return $contentItems;
    }

    protected function appendMessage(array $contentItems): array
    {
        if ($this->message === null) {
            return $contentItems;
        }

        $contentItems['message'] = sprintf('message="%s"', $this->message);

        return $contentItems;
    }
}
