<?php
namespace Klepto\Traits;

trait UrifyStr
{
    public function urifyStr(string $str):string
    {
        return preg_replace(
            "#([^a-zA-Z0-9\-]+)#",
            ".",
            $str
        );
    }
}
