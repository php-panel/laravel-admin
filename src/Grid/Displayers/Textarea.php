<?php

namespace Ladmin\Grid\Displayers;

use Ladmin\Admin;

class Textarea extends AbstractDisplayer
{
    public function display($rows = 5)
    {
        return Admin::component('admin::grid.inline-edit.textarea', [
            'key'      => $this->getKey(),
            'value'    => $this->getValue(),
            'display'  => $this->getValue(),
            'name'     => $this->getPayloadName(),
            'resource' => $this->getResource(),
            'trigger'  => "ie-trigger-{$this->getClassName()}",
            'target'   => "ie-template-{$this->getClassName()}",
            'rows'     => $rows,
        ]);
    }
}
