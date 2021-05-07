<?php

class NoNamespace
{
    public function run()
    {
        return new class {
            public function execute()
            {
            }
        };
    }
}
-----
<?php

class NoNamespace
{
    public function run()
    {
        return new Anonymous__%s__0();
    }
}
class Anonymous__%s__0
{
    public function execute()
    {
    }
}
