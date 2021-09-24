<?php

namespace Manalard\Utilities;

use ReflectionClass;

if (!class_exists('Manalard\Utilities\HTML')) :
    /**
     * Class Manalard\Utilities\HTML
     *
     * a Simple HTML Helper Class to generate form field.
     * @version 1.0.1
     * @author  Harish Dasari
     * @link    http://github.com/harishdasari
     * @contributor Batur Kacamak
     * @link    https://github.com/baturkacamak
     * @package Manalard
     */
    class HTML
    {

        protected $optionTypes;

        /**
         * Returns the Form Table html
         *
         * @param array $fields Input fields options
         * @param boolean $show_help Show or hide help string
         *
         * @return string             HTML string
         */
        public function getFormTable($fields, $show_help = true)
        {
            $form_table = '';

            $form_table .= '<table class="form-table">';

            foreach ((array)$fields as $field) {
                $form_table .= $this->getFormRow($field, $show_help);
            }

            $form_table .= '</table>';

            return apply_filters('hd_html_helper_form_table', $form_table, $fields, $show_help);
        }

        /**
         * Echo/Display the HTML Form table
         *
         * @param array $fields Input fields options
         * @param boolean $show_help Show or hide help string
         *
         * @return null
         */
        public function showFormTable($fields, $show_help = true)
        {
            echo $this->getFormTable($fields, $show_help);
        }

        /**
         * Returns the table row html
         *
         * @param array $field
         * @param boolean $show_help
         *
         * @return string
         */
        public function getFormRow($field, $show_help)
        {
            $table_row = '<tr valign="top">';
            $table_row .= sprintf('<th><label for="%s">%s</label></th>', esc_attr($field['id']), $field['title']);
            $table_row .= sprintf('<td>%s</td>', $this->getField($field, $show_help));
            $table_row .= '</tr>';

            return apply_filters('hd_html_helper_table_row', $table_row, $field, $show_help);
        }

        /**
         * returns a input field based on field options
         *
         * @param array $field Input field options
         * @param boolean $show_help Show or hide help string
         *
         * @return string             HTML string
         */
        public function getField($field, $show_help = true)
        {
            $field_default = [
                'title'    => '',
                'id'       => '',
                'type'     => '',
                'default'  => '',
                'choices'  => [],
                'value'    => '',
                'desc'     => '',
                'sanit'    => '',
                'multiple' => false, // for multiselect fiield
            ];

            $field = wp_parse_args($field, $field_default);

            $option_type = ucfirst($field['type']);

            if (!isset($this->optionTypes[$option_type])) {
                $class_name = "\Manalard\OptionTypes\\$option_type";

                if (!class_exists($class_name)) {
                    throw new \Exception("{$option_type} not found");
                }

                $reflection_class                = new ReflectionClass($class_name);
                $this->optionTypes[$option_type] = $reflection_class->newInstance();
            }


            return $this->optionTypes[$option_type]->render($field);


            if ($show_help && 'checkbox' !== $field['type']) {
                $input_html .= $this->getHelpText($field);
            }

            return apply_filters('hd_html_helper_input_field', $input_html, $field, $show_help);
        }

        /**
         * Displays a Input field based field options
         *
         * @param array $field Input field options
         * @param boolean $show_help Show or hide help string
         *
         * @return null
         */
        public function displayField($field, $show_help = true)
        {
            echo $this->getField($field, $show_help);
        }

        /**
         * Get HTML attributes from attributes array,
         *
         * @param array $attrs Attributes array.
         *
         * @return string
         */
        public function generateHtmlAttrs($attrs)
        {
            if (empty($attrs)) {
                return '';
            }

            $html_attrs = [];

            foreach ($attrs as $key => $value) {
                $html_attrs[] = sprintf('%s="%s"', esc_attr($key), esc_attr($value));
            }

            return implode(' ', $html_attrs);
        }

        /**
         * Print Text Input
         *
         * @param array $field Input Options
         *
         * @return null
         */
        private function getTextInput($field)
        {
            return sprintf(
                '<input type="text" name="%s" id="%s" value="%s" class="regular-text"/>',
                esc_attr($field['id']),
                esc_attr($field['id']),
                esc_attr($field['value'])
            );
        }

        /**
         * Number Input
         *
         * @param array $field Input Options
         *
         * @return string
         */
        private function getNumberInput($field)
        {
            $attrs = isset($field['attrs']) ? (array)$field['attrs'] : [];
            $attrs = wp_parse_args(
                $attrs,
                [
                    'class' => 'small-text',
                ]
            );

            $attrs['type']  = 'number';
            $attrs['id']    = $field['id'];
            $attrs['name']  = $field['id'];
            $attrs['value'] = $field['value'];

            return sprintf('<input %s>', $this->generateHtmlAttrs($attrs));
        }

        /**
         * Print Textarea Input
         *
         * @param array $field Input Options
         *
         * @return null
         */
        private function getTextareaInput($field)
        {
            return sprintf(
                '<textarea name="%s" id="%s" rows="5" cols="40">%s</textarea>',
                esc_attr($field['id']),
                esc_attr($field['id']),
                esc_textarea($field['value'])
            );
        }

        /**
         * Print Select Input
         *
         * @param array $field Input Options
         *
         * @return null
         */
        private function getSelectInput($field)
        {
            $selected_value = $field['value'];

            $multiple = (true == $field['multiple'] || 'true' == $field['multiple']) ? true : false;

            if ($multiple) {
                $field['id'] = $field['id'] . '[]';
            }

            $select_field = sprintf(
                '<select name="%s" id="%s"%s>',
                esc_attr($field['id']),
                esc_attr($field['id']),
                ($multiple ? ' multiple' : '')
            );

            if (!empty($field['choices'])) {
                foreach ((array)$field['choices'] as $value => $label) {
                    $selected     = $multiple ? selected(
                        in_array($value, (array)$selected_value),
                        true,
                        false
                    ) : selected(
                        $selected_value,
                        $value,
                        false
                    );
                    $select_field .= sprintf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr($value),
                        $selected,
                        esc_html($label)
                    );
                }
            }

            $select_field .= '</select>';

            return $select_field;
        }

        /**
         * Print Checkbox Input
         *
         * @param array $field Input Options
         *
         * @return null
         */
        private function getCheckboxInput($field)
        {
            return sprintf(
                '<label><input type="checkbox" name="%s" id="%s"%s> %s</label>',
                esc_attr($field['id']),
                esc_attr($field['id']),
                checked($field['value'], 'on', false),
                esc_html($field['desc'])
            );
        }

        /**
         * Print Radio Input
         *
         * @param array $field Input Options
         *
         * @return null
         */
        private function getRadioInput($field)
        {
            $selected_value = $field['value'];

            $radio_field = '';

            if (!empty($field['choices'])) {
                foreach ((array)$field['choices'] as $value => $label) {
                    $radio_field .= sprintf(
                        '<label><input type="radio" name="%s" id="" value="%s"%s> %s</label><br/>',
                        esc_attr($field['id']),
                        esc_attr($value),
                        checked($selected_value, $value, false),
                        esc_html($label)
                    );
                }
            }

            return $radio_field;
        }

        /**
         * Print Multi-Checkbox Input
         *
         * @param array $field Input Options
         *
         * @return null
         */
        private function getMulticheckInput($field)
        {
            $selected_value = (array)$field['value'];

            $multicheck_field = '';

            if (!empty($field['choices'])) {
                foreach ((array)$field['choices'] as $value => $label) {
                    $multicheck_field .= sprintf(
                        '<label><input type="checkbox" name="%s[]" id="" value="%s"%s> %s</label><br/>',
                        esc_attr($field['id']),
                        esc_attr($value),
                        checked(in_array($value, $selected_value), true, false),
                        esc_html($label)
                    );
                }
            }

            return $multicheck_field;
        }

        /**
         * Print Upload Input
         *
         * @param array $field Input Options
         *
         * @return null
         */
        private function getUploadInput($field)
        {
            // dang! dang!! dang!!!
            // We require to enqueue Media Uploader Scripts and Styles
            wp_enqueue_media();

            return sprintf(
                '<input type="text" name="%s" id="%s" value="%s" class="regular-text hd-upload-input"/>' .
                '<input type="button" value="%s" class="hd-upload-button button button-secondary" id="hd_upload_%s"/>',
                esc_attr($field['id']),
                esc_attr($field['id']),
                esc_attr($field['value']),
                __('Upload'),
                esc_attr($field['id'])
            );
        }

        /**
         * Print Color Picker Input
         *
         * @param array $field Input Options
         *
         * @return null
         */
        private function getColorInput($field)
        {
            $default_color = empty($field['default']) ? '' : ' data-default-color="' . esc_attr(
                    $field['default']
                ) . '"';

            return sprintf(
                '<input type="text" name="%s" id="%s" value="%s" class="hd-color-picker"%s/>',
                esc_attr($field['id']),
                esc_attr($field['id']),
                esc_attr($field['value']),
                $default_color
            );
        }

        /**
         * Print TinyMCE Editor Input
         *
         * @param array $field Input Options
         *
         * @return null
         */
        private function showEditorInput($field)
        {
            $settings = [
                'media_buttons' => false,
                'textarea_rows' => 5,
                'textarea_cols' => 45,
            ];

            $content = $field['value'];
            $content = empty($content) ? '' : $content;

            ob_start();
            wp_editor($content, $field['id'], $settings);

            return ob_get_clean();
        }

        /**
         * Print Help/Descripting for field
         *
         * @param array $field Input Options
         *
         * @return (string|null)
         */
        private function getHelpText($field)
        {
            if (empty($field['desc'])) {
                return '';
            }

            return '<p class="description">' . wp_kses_data($field['desc']) . '</p>';
        }

    } // End Helpers

endif; // end class_exists check
