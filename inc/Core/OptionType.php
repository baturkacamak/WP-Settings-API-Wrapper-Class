<?php


namespace Manalard\Core;


abstract class OptionType
{
    abstract public function getType();

    abstract protected function renderItem($options);

    abstract protected function getDefaults();

    protected function attsToHtml($atts)
    {
        $html_attr = '';

        foreach ($atts as $attr_name => $attr_val) {
            if (false === $attr_val || empty($attr_val)) {
                continue;
            }

            $html_attr .= $attr_name . '="' . htmlspecialchars($attr_val, ENT_QUOTES, 'UTF-8') . '" ';
        }

        return $html_attr;
    }

    protected function prepare(&$options)
    {
        $options['atts']['name'] = $options['id'];
        // get default value
        $default = ml_atv('default', $options, '');
        // if no input value, use default
        $options['atts']['value'] = ml_atv('value', $options, $default, true);
    }

    protected function hasValue($value)
    {
        return !empty($value);
    }

    protected function renderBefore($options)
    {
        return '';
    }

    protected function renderAfter($options)
    {
        if (isset($options['desc'])) {
            return "<div>{$options['desc']}</div>";
        }

        return '';
    }


    public function render($options)
    {
        $this->prepare($options);

        $before        = $this->renderBefore($options);
        $element_input = $this->renderItem($options);
        $after         = $this->renderAfter($options);

        return $before . $element_input . $after;
    }

}