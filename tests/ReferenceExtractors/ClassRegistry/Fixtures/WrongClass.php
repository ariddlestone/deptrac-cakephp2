<?php

class ModelClass extends Model {}

class OtherClass
{
    public function otherClassFunction()
    {
        WrongClass::init('ModelClass');
    }
}

class WrongClass
{
    public static function init($name) {}
}
