<?php

namespace Ladmin\Form\Field;

use Ladmin\Form\Field;

class Button extends Field
{
    protected $class = 'btn-primary';

    public function info()
    {
        $this->class = 'btn-info';

        return $this;
    }

    public function on($event, $callback)
    {
        $this->script = <<<EOT

        $('{$this->getElementClassSelector()}').on('$event', function() {
            $callback
        });

EOT;
    }
}
