<?php

function inFunctionNoNamespace()
{
    return new class {
            public function execute()
            {
            }
    };
}
-----
<?php

function inFunctionNoNamespace()
{
    return new Anonymous__%s__0();
}
class Anonymous__%s__0
{
    public function execute()
    {
    }
}
