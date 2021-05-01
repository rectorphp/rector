<?php

final class AddErrorString
{
    public function get()
    {
        return ['error' => 100];
    }
}

?>
-----
<?php

final class AddErrorString
{
    /**
     * @return array<string, int>
     */
    public function get()
    {
        return ['error' => 100];
    }
}

?>
