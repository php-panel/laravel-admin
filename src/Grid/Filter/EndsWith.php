<?php

namespace Ladmin\Grid\Filter;

class EndsWith extends Like
{
    protected $exprFormat = '%{value}';
}
