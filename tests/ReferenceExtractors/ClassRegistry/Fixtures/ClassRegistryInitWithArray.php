    <?php

    class ModelClass extends Model {}

    class OtherClass
    {
        public function otherClassFunction()
        {
            ClassRegistry::init(['alias' => 'ModelAlias', 'class' => 'ModelClass']);
        }
    }
