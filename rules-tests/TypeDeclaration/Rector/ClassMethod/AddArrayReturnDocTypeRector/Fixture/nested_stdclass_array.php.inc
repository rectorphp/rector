<?php

final class NestedStdclassArray
{
    public function createItems($items)
    {
        return $this->convertVideos($items);
    }

    public function convertVideos($items): array
    {
        $objects = [];
        foreach ($items as $item) {
            $objects[] = $this->create($item);
        }

        return $objects;
    }

    public function create($item): stdClass
    {
        $object = new stdClass();
        $object->name = $item->name;
        return $object;
    }
}

?>
-----
<?php

final class NestedStdclassArray
{
    /**
     * @return \stdClass[]
     */
    public function createItems($items)
    {
        return $this->convertVideos($items);
    }

    /**
     * @return \stdClass[]
     */
    public function convertVideos($items): array
    {
        $objects = [];
        foreach ($items as $item) {
            $objects[] = $this->create($item);
        }

        return $objects;
    }

    public function create($item): stdClass
    {
        $object = new stdClass();
        $object->name = $item->name;
        return $object;
    }
}

?>
