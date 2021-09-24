<?php


namespace Manalard\OptionTypes;


use Manalard\Core\OptionType;

class Select extends OptionType
{

    public function getType()
    {
        return 'select';
    }

    private function getSelected($multiple, $value, $selectedValue)
    {
        if ($multiple) {
            return (bool)selected(
                in_array($value, (array)$selectedValue),
                true,
                false
            ) ? 'selected' : false;
        }

        return (bool)selected(
            $selectedValue,
            $value,
            false
        ) ? 'selected' : false;
    }

    protected function renderItem($options)
    {
        $selected_value = $options['value'];
        unset($options['atts']['value']);

        $multiple = (true == $options['multiple'] || 'true' == $options['multiple']) ? true : false;

        if ($multiple) {
            $options['atts']['name'] = $options['id'] . '[]';
            $options['atts']['multiple'] = 'multiple';
        }

        $select_field = "<select {$this->attsToHtml($options['atts'])}>";

        if (!empty($options['choices'])) {
            foreach ((array)$options['choices'] as $value => $label) {
                $selected    = $this->getSelected($multiple, $value, $options['value']);
                $label       = esc_html($label);
                $option_atts = ['value' => esc_attr($value), 'selected' => $selected];

                $select_field .= "<option {$this->attsToHtml($option_atts)}>{$label}</option>";
            }
        }

        $select_field .= '</select>';

        return $select_field;
    }

    protected function getDefaults()
    {
        // TODO: Implement getDefaults() method.
    }
}