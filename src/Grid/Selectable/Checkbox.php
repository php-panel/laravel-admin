<?php

namespace Ladmin\Grid\Selectable;

use Ladmin\Grid\Displayers\AbstractDisplayer;

class Checkbox extends AbstractDisplayer
{
    public function display($key = '')
    {
        $value = $this->getAttribute($key);

        return <<<EOT
<input type="checkbox" name="item" class="select" value="{$value}"/>
EOT;
    }
}
