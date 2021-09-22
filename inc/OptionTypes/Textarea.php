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
        return "<textarea {$this->attsToHtml($options['atts'])}>{$options['value']}</textarea>";
    }

    protected function getDefaults()
    {
        // TODO: Implement getDefaults() method.
    }
}