<?php

function () {
    return new class {
            public function execute()
            {
            }
    };
};

?>
-----
<?php

function () {
    return new Anonymous__%s__0();
};
class Anonymous__%s__0
{
    public function execute()
    {
    }
}
