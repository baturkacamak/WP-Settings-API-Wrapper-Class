<?php


namespace Manalard\OptionTypes;


class Textarea extends \Manalard\Core\OptionType
{

    public function getType()
    {
        return 'textarea';
    }

    protected function renderItem($options)
    {
        $value = $options['atts']['value'];
        unset($options['atts']['value']);

        return "<textarea {$this->attsToHtml($options['atts'])}>{$value}</textarea>";
    }

    protected function getDefaults()
    {
        // TODO: Implement getDefaults() method.
    }
}