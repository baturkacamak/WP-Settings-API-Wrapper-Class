<?php

if (!function_exists('ml_atv')) {
    /**
     * @param string $selectors
     * @param array $array
     * @param null $default_value
     * @param bool $show_empty
     * @param string $delimiter
     *
     * @return mixed|null
     */
    function ml_atv($selectors, $array, $default_value = null, $show_empty = false, $delimiter = '/')
    {
        if (!is_array($selectors)) {
            $selectors = explode($delimiter, (string)$selectors);
        }

        $array_key = array_shift($selectors);

        if (isset($selectors[0])) {
            return ml_atv($selectors, $array[$array_key], $default_value, $show_empty);
        } else {
            if (isset($array[$array_key])) {
                if ($show_empty) {
                    return $array[$array_key];
                }

                if (!empty($array[$array_key]) && !$show_empty) {
                    return $array[$array_key];
                }
            }

            return $default_value;
        }
    }
}
