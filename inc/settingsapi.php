<?php
/*
Name: WordPress Settings API Wrapper Class
URI: https://github.com/baturkacamak
Description: A PHP Library for creating WordPress Option Pages with tabs using WordPress Settings API
Author: Harish Dasari
Author URI: http://twitter.com/harishdasari
Contributor: Batur Kacamak
Contributor URI: https://batur.info
Version: 1.1
License: GNU General Public License v2.0 or later
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

/*  Copyright 2014 Harish Dasari  (email : harishdasari@outlook.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*=================================================================================
	WordPress Settings API Wrapper Class
 =================================================================================*/

namespace Manalard;

use \Illuminate\Support\Collection;

use function collect;

require_once __DIR__ . '/../vendor/autoload.php';

require_once('helpers.php');

if (!class_exists('Manalard\SettingsAPI')) :
    /**
     * WordPress Settings API Wrapper Class
     *
     * @version 1.1
     */
    class SettingsAPI
    {

        /**
         * Holds Options for Menu Page
         * @var array
         */
        var $options = [];

        /**
         * Holds Settings fields data
         * @var array
         */
        var $fields = [];

        /**
         * Holds Tab Options
         * @var array
         */
        var $tabs = [];

        /**
         * Holds Section ID to addubg settings fields
         * @var string
         */
        var $currentSection = 'default';

        /**
         * Holds Tab ID to adding settings sections and settings fields
         * @var boolean/string
         */
        var $currentTab = false;

        /**
         * Holds Active Tab ID
         * @var boolean/string
         */
        var $activeTab = false;

        /**
         * Holds Current field data to adding settings field
         * @var mixed
         */
        var $currentField = false;

        /**
         * Holds Menu page $hook_suffix
         * @var boolean/string
         */
        var $hookSuffix = false;

        /**
         * Holds instance of Manalard\Helpers class
         * @var object
         */
        var $htmlHelper;

        /**
         * Holds Current Folder Path
         * @var string
         */
        var $dirPath;

        /**
         * Holds Current Folder URI
         * @var string
         */
        var $dirUri;

        /**
         * Constructor
         *
         * @param array $options Array containing the necessary params.
         *    $options = [
         *      'page_title'    => (string) Page Title. Required
         *      'menu_title'    => (string) Menu Title. Required
         *      'menu_slug'     => (string) Menu Slug. Required
         *      'parent_slug'   => (string) Parent Slug.
         *      'capability'    => (string)
         *      'icon'          => (string)
         *      'position'      => (int)
         *    ]
         *
         * @return bool
         */
        public function __construct($options = [])
        {
            $this->options = $this->getOptions($options);

            extract($this->options->all());
            // Titles should not be empty
            if (empty($page_title) || empty($menu_title)) {
                return false;
            }

            // Set directory path
            $this->dirPath = str_replace('\\', '/', __DIR__);

            // Set directory uri
            $this->dirUri = trailingslashit(home_url()) . str_replace(
                    str_replace('\\', '/', ABSPATH),
                    '',
                    $this->dirPath
                );

            $this->htmlHelper = class_exists('Manalard\Helpers') ? new Helpers() : false;

            $this->handle();
        }

        /**
         * @param $options
         *
         * @return Collection
         */
        private function getOptions($options)
        {
            // Default page options
            $options_default = [
                'page_title'  => '',
                'menu_title'  => '',
                'menu_slug'   => '',
                'parent_slug' => '',
                'capability'  => 'manage_options',
                'icon'        => 'dashicons-admin-generic',
                'position'    => null,
            ];

            $options = collect(wp_parse_args($options, $options_default));

            if (empty($options->get('menu_slug'))) {
                $menu_slug = sanitize_title($options->get('menu_title'));
                $options->put('menu_slug', $menu_slug);
            }

            return $options;
        }

        /**
         *
         */
        public function handle()
        {
            add_action('admin_menu', [$this, 'registerMenu']);
            add_action('admin_init', [$this, 'registerOptions']);
            add_action('admin_enqueue_scripts', [$this, 'enqueueAdmin']);
            add_action('admin_notices', [$this, 'showNotices']);
        }


        /**
         * Register a New Menu Page
         *
         * @return void
         */
        public function registerMenu()
        {
            // Collect all tabs
            foreach ($this->fields as $field_setting => $field) {
                if ('tab' === $field['type']) {
                    $this->tabs[sanitize_title($field_setting)] = $field['title'];
                }
            }

            // Set active tab
            if (!empty($this->tabs)) {
                if (isset($_GET['tab']) && array_key_exists($_GET['tab'], (array)$this->tabs)) {
                    $this->activeTab = $_GET['tab'];
                } elseif (isset($_REQUEST[$this->options->get('menu_slug') . '_active_tab']) && array_key_exists(
                        $_REQUEST[$this->options->get('menu_slug') . '_active_tab'],
                        (array)$this->tabs
                    )) {
                    $this->activeTab = $_REQUEST[$this->options->get('menu_slug') . '_active_tab'];
                } else {
                    $tab_keys        = array_keys((array)$this->tabs);
                    $this->activeTab = reset($tab_keys);
                }
            }

            extract($this->options->all());

            if (empty($parent_slug)) {
                $this->hookSuffix = add_menu_page(
                    $page_title,
                    $menu_title,
                    $capability,
                    $menu_slug,
                    [$this, 'showSettingsPage'],
                    $icon,
                    $position
                );
            } else {
                $this->hookSuffix = add_submenu_page(
                    $parent_slug,
                    $page_title,
                    $menu_title,
                    $capability,
                    $menu_slug,
                    [$this, 'showSettingsPage']
                );
            }
        }

        /**
         * Enqueue Styles and Scripts
         *
         * @param string $hook_suffix
         *
         * @return void
         */
        public function enqueueAdmin($hook_suffix)
        {
            if ($this->hookSuffix !== $hook_suffix) {
                return;
            }

            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script(
                'hd-html-helper',
                $this->dirUri . '/js/admin.js',
                ['jquery', 'wp-color-picker'],
                null,
                true
            );
        }

        /**
         * @param array $fields
         */
        public function setFields($fields)
        {
            $this->fields = $fields;
        }


        /**
         * Register Sections, Fields and Settings
         *
         * @return void
         */
        public function registerOptions()
        {
            foreach ($this->fields as $field_setting => $field) {
                $field['id'] = $field_setting;

                $this->currentField = $field;

                if ('tab' === $field['type']) {
                    $this->currentTab     = $field['id'];
                    $this->currentSection = 'default';
                } elseif ('section' === $field['type']) {
                    $this->currentSection = empty($field['id']) ? 'default' : $field['id'];

                    if (empty($this->currentTab)) {
                        add_settings_section(
                            $field['id'],
                            $field['title'],
                            [$this, 'showSection'],
                            $this->options->get('menu_slug')
                        );
                    } else {
                        add_settings_section(
                            $field['id'],
                            $field['title'],
                            [$this, 'showSection'],
                            $this->options->get('menu_slug') . '_' . $this->currentTab
                        );
                    }
                } else {
                    // Set Field Value
                    $field['value'] = get_option($field['id']);

                    if (empty($this->currentTab)) {
                        add_settings_field(
                            $field['id'],
                            $field['title'],
                            [$this->htmlHelper, 'displayField'],
                            $this->options->get('menu_slug'),
                            $this->currentSection,
                            $field
                        );
                    } else {
                        add_settings_field(
                            $field['id'],
                            $field['title'],
                            [$this->htmlHelper, 'displayField'],
                            $this->options->get('menu_slug') . '_' . $this->currentTab,
                            $this->currentSection,
                            $field
                        );
                    }

                    if (empty($this->currentTab) || $this->currentTab === $this->activeTab) {
                        register_setting($this->options->get('menu_slug'), $field['id'], [$this, 'sanitize_setting']);
                    }

                    if (!empty($field['default'])) {
                        add_option($field['id'], $field['default']);
                    }
                }
            }
        }

        /**
         * Show Admin Notices
         *
         * @return void
         */
        public function showNotices()
        {
            global $parent_file;

            if ('options-general.php' === $parent_file) {
                return;
            }

            if (isset($_GET['page']) && $_GET['page'] === $this->options->get('menu_slug')) {
                settings_errors();
            }
        }

        /**
         * Print Settings Page
         *
         * @return void
         */
        public function showSettingsPage()
        {
            $this->render('settings-page', [], true);
        }

        /**
         * Render the specified view.
         *
         * @param string $view
         * @param array $data
         * @param mixed $echo
         *
         * @return string
         */
        protected function render($view = null, $data = [], $echo = false)
        {
            if (is_array($view) && empty($data)) {
                $data = $view;
                $view = null;
            }

            if (empty($data)) {
                $data = $this->options->all();
            }

            extract($data);
            ob_start();
            include trailingslashit(__DIR__) . "../resources/views/{$view}.php";
            if ($echo) {
                echo ob_get_clean();
            } else {
                return ob_get_clean();
            }
        }

        /**
         * Print Settings Section
         *
         * @param array $args Section Options
         *
         * @return void
         */
        public function showSection($args)
        {
            if (isset($this->fields[$args['id']]['desc'])) {
                echo $this->fields[$args['id']]['desc'];
            }
        }

        /**
         * Sanitize settings
         *
         * @param mixed $new_value Submitted new value
         *
         * @return mixed            Sanitized value
         */
        public function sanitizeSetting($new_value)
        {
            $setting = str_replace('sanitize_option_', '', current_filter());

            $field = $this->fields[$setting];

            if (!isset($field['sanit'])) {
                $field['sanit'] = '';
            }

            switch ($field['sanit']) {
                case 'int' :
                    return is_array($new_value) ? array_map('intval', $new_value) : intval($new_value);
                    break;

                case 'absint' :
                    return is_array($new_value) ? array_map('absint', $new_value) : absint($new_value);
                    break;

                case 'email' :
                    return is_array($new_value) ? array_map('sanitize_email', $new_value) : sanitize_email($new_value);
                    break;

                case 'url' :
                    return is_array($new_value) ? array_map('esc_url_raw', $new_value) : esc_url_raw($new_value);
                    break;

                case 'bool' :
                    return (bool)$new_value;
                    break;

                case 'color' :
                    return $this->sanitizeHexColor($new_value);
                    break;

                case 'html' :
                    if (current_user_can('unfiltered_html')) {
                        return is_array($new_value) ? array_map('wp_kses_post', $new_value) : wp_kses_post($new_value);
                    } else {
                        return is_array($new_value) ? array_map('wp_strip_all_tags', $new_value) : wp_strip_all_tags(
                            $new_value
                        );
                    }
                    break;

                case 'nohtml' :
                    return is_array($new_value) ? array_map('wp_strip_all_tags', $new_value) : wp_strip_all_tags(
                        $new_value
                    );
                    break;

                default :
                    return apply_filters('hd_settings_api_sanitize_option', $new_value, $field, $setting);
                    break;
            }
        }

        /**
         * Sanitize Hex Color (taken from WP Core)
         *
         * @param string $color Hex Color
         *
         * @return mixed         Sanitized Hex Color or null
         */
        public function sanitizeHexColor($color)
        {
            if ('' === $color) {
                return '';
            }

            if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
                return $color;
            }

            return null;
        }

    } // SettingsAPI end

endif; // class_exists check
