<div class="wrap <?php echo sanitize_html_class($menu_slug); ?>">

    <h2><?php echo esc_html($this->options['page_title']); ?></h2>

    <form action="<?php echo admin_url('options.php') ?>"
          method="post"
    >

        <?php settings_fields($menu_slug); ?>

        <?php do_action(
            'hd_settings_api_page_before',
            $this->hookSuffix,
            $this->options,
            $this->fields
        ); ?>

        <table class="form-table">
            <?php do_settings_fields($menu_slug, 'default'); ?>
        </table>
        <?php do_settings_sections($menu_slug); ?>
        <?php if (!empty($this->tabs)) { ?>
            <h2 class="nav-tab-wrapper">
                <?php
                foreach ((array)$this->tabs as $tab_id => $tab_name) {
                    printf(
                        '<a href="%s" class="nav-tab%s">%s</a>',
                        add_query_arg(['page' => $menu_slug, 'tab' => $tab_id]),
                        ($this->activeTab === $tab_id) ? ' nav-tab-active' : '',
                        esc_html($tab_name)
                    );
                }
                ?>
            </h2>

            <?php do_action(
                'hd_settings_api_tab_before',
                $this->hookSuffix,
                $this->activeTab,
                $this->options,
                $this->fields
            ); ?>

            <table class="form-table">
                <?php do_settings_fields(
                    $menu_slug . '_' . $this->activeTab,
                    'default'
                ); ?>
            </table>

            <?php do_settings_sections($menu_slug . '_' . $this->activeTab); ?>

            <?php do_action(
                'hd_settings_api_tab_after',
                $this->hookSuffix,
                $this->activeTab,
                $this->options,
                $this->fields
            ); ?>

            <input type="hidden"
                   name="<?php echo esc_attr($menu_slug . '_active_tab'); ?>"
                   value="<?php echo esc_attr($this->activeTab); ?>"
            />

        <?php } ?>

        <?php do_action('hd_settings_api_page_after', $this->hookSuffix, $this->options, $this->fields); ?>

        <div class="clear"></div>

        <?php submit_button(apply_filters('hd_settings_api_save_button_text', __('Save Changes'))); ?>

    </form>

</div>