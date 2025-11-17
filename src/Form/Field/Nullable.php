<?php

namespace Ladmin\Form\Field;

use Ladmin\Form\Field;

class Nullable extends Field
{
    public function __construct() {}

    public function __call($method, $parameters)
    {
        return $this;
    }
}
