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
    }

    public function render($options)
    {
        $this->prepare($options);


        return $this->renderItem($options);
    }

}