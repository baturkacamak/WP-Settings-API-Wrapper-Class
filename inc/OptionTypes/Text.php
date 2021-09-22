<?php


namespace Manalard\OptionTypes;


use Manalard\Core\OptionType;

if (!class_exists('Manalard\OptionTypes\Text')) {
    class Text extends OptionType
    {

        public function getType()
        {
            return 'input';
        }

        protected function renderItem($options)
        {
            $option['atts']['type'] = 'text';

            return "<input {$this->attsToHtml($options['atts'])} />";
        }

        protected function getDefaults()
        {
        }
    }
}
