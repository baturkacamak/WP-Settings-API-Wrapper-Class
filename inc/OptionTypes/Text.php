<?php


namespace Manalard\OptionTypes;


use Manalard\Core\OptionType;


if (!class_exists('Manalard\OptionTypes\Text')) {
    class Text extends OptionType
    {

        public function getType()
        {
            return 'text';
        }

        protected function renderItem($options)
        {
            $options['atts']['type'] = ml_atv('atts/type', $options, $this->getType());

            return "<input {$this->attsToHtml($options['atts'])} />";
        }

        protected function getDefaults()
        {
        }
    }
}
