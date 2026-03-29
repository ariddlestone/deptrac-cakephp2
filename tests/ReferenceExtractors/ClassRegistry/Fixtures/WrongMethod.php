<?php

class ModelClass extends Model {}

class OtherClass
{
    public function otherClassFunction()
    {
        ClassRegistry::getObject('ModelClass');
    }
}
