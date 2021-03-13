<?php

class AnotherMyOldClass
{
    /**
     * @return AnotherMyOldClass
     */
    public function createSelf(): AnotherMyOldClass
    {
        return new AnotherMyOldClass;
    }
}

?>
-----
<?php

class AnotherMyNewClass
{
    /**
     * @return \AnotherMyNewClass
     */
    public function createSelf(): \AnotherMyNewClass
    {
        return new \AnotherMyNewClass;
    }
}

?>
