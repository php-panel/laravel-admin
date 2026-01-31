<?php

namespace Ladmin\Grid\Filter;

use Ladmin\Admin;
use Illuminate\Support\Arr;

class Between extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected $view = 'admin::filter.between';
    
    /**
     * 是否使用 MongoDB 模式
     * 
     * @var bool
     */
    protected $mongoMode = false;
    
    /**
     * 是否转换日期格式为 UTCDateTime
     * 
     * @var bool
     */
    protected $convertDate = false;

    /**
     * Format id.
     *
     * @param string $column
     *
     * @return array|string
     */
    public function formatId($column)
    {
        $id = str_replace('.', '_', $column);

        return ['start' => "{$id}_start", 'end' => "{$id}_end"];
    }

    /**
     * Format two field names of this filter.
     *
     * @param string $column
     *
     * @return array
     */
    protected function formatName($column)
    {
        $columns = explode('.', $column);

        if (count($columns) == 1) {
            $name = $columns[0];
        } else {
            $name = array_shift($columns);

            foreach ($columns as $column) {
                $name .= "[$column]";
            }
        }

        return ['start' => "{$name}[start]", 'end' => "{$name}[end]"];
    }

    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @return mixed
     */
    public function condition($inputs)
    {
        if ($this->ignore) {
            return;
        }

        if (!Arr::has($inputs, $this->column)) {
            return;
        }

        $this->value = Arr::get($inputs, $this->column);

        $value = array_filter($this->value, function ($val) {
            return $val !== '';
        });

        if (empty($value)) {
            return;
        }

        if (!isset($value['start'])) {
            return $this->buildCondition($this->column, '<=', $value['end']);
        }

        if (!isset($value['end'])) {
            return $this->buildCondition($this->column, '>=', $value['start']);
        }

        $this->query = 'whereBetween';
        // MongoDB 模式：需要将关联数组转为索引数组
        if ($this->mongoMode) {
            $startValue = $value['start'];
            $endValue = $value['end'];
            
            // 如果启用日期转换，将字符串转为 UTCDateTime
            if ($this->convertDate && class_exists('\MongoDB\BSON\UTCDateTime')) {
                if (is_string($startValue)) {
                    $startValue = new \MongoDB\BSON\UTCDateTime(strtotime($startValue) * 1000);
                }
                if (is_string($endValue)) {
                    // 结束日期加上 23:59:59
                    $endStr = strlen($endValue) === 10 ? $endValue . ' 23:59:59' : $endValue;
                    $endValue = new \MongoDB\BSON\UTCDateTime(strtotime($endStr) * 1000);
                }
            }
            
            // MongoDB whereBetween 需要索引数组 [min, max]
            return $this->buildCondition($this->column, [$startValue, $endValue]);
        }

        return $this->buildCondition($this->column, $this->value);
    }
    
    /**
     * 启用 MongoDB 模式
     * 
     * @param bool $convertDate 是否转换日期为 UTCDateTime
     * @return $this
     */
    public function mongo($convertDate = false)
    {
        $this->mongoMode = true;
        $this->convertDate = $convertDate;
        return $this;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function datetime($options = [])
    {
        $this->view = 'admin::filter.betweenDatetime';

        $this->setupDatetime($options);

        return $this;
    }

    /**
     * @param array $options
     */
    protected function setupDatetime($options = [])
    {
        $options['format'] = Arr::get($options, 'format', 'YYYY-MM-DD HH:mm:ss');
        $options['locale'] = Arr::get($options, 'locale', config('app.locale'));

        $startOptions = json_encode($options);
        $endOptions = json_encode($options + ['useCurrent' => false]);

        $script = <<<EOT
            $('#{$this->id['start']}').datetimepicker($startOptions);
            $('#{$this->id['end']}').datetimepicker($endOptions);
            $("#{$this->id['start']}").on("dp.change", function (e) {
                $('#{$this->id['end']}').data("DateTimePicker").minDate(e.date);
            });
            $("#{$this->id['end']}").on("dp.change", function (e) {
                $('#{$this->id['start']}').data("DateTimePicker").maxDate(e.date);
            });
EOT;

        Admin::script($script);
    }
}
