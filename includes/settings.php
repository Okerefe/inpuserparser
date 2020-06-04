<?php declare(strict_types = 1);

namespace InpUserParser;
/*
 * Handles Display of InpUserSettings Page
 * Handles Saving and Retrieving of Options
 * */

class Settings
{
    const SETTINGS_PAGE = 'inpuserparser';
    const SEARCH_SECTION_ID = 'inpuserparser_search_settings';
    const COLUMN_SECTION_ID = 'inpuserparser_column_settings';
    const OPTION_NAME = 'inpuserparser_options';

//     Set Necessary Wordpress Hooks
    public static function init()
    {
        add_action('admin_menu', ['InpUserParser\Settings', 'addMenu']);
        add_action('admin_init', ['InpUserParser\Settings', 'registerSettings']);
        add_filter(
            'plugin_action_links_inpuserparser/inpuserparser.php',
            ['InpUserParser\Settings', 'addSettingsLink']
        );
    }

    public static function install()
    {
    }

//    Generate Link to InpUserSettings Page
    public static function getSettingsLink() : string
    {
        $url = esc_url(add_query_arg(
            'page',
            self::SETTINGS_PAGE,
            get_admin_url() . 'admin.php'
        ));
        // Create the link.
        return "<a href='$url'>Settings</a>";
    }

//    Add Link to InpUserSettings on plugin page
    public static function addSettingsLink(array $links) : array
    {
        // Adds the link to the end of the array.
        array_push(
            $links,
            self::getSettingsLink()
        );
        return $links;
    }

    public static function uninstall()
    {
        delete_option(self::OPTION_NAME);
        delete_transient(User::USERS_TRANSIENT);
    }

//    Reformat camelCased Strings
    public static function ucFields(string $field) : string
    {
        return ucwords(preg_replace('/(?<!\ )[A-Z]/', ' $0', $field));
    }

//    Add Plugin to to subMenu
    public static function addMenu()
    {
        add_submenu_page(
            'options-general.php',
            'InpUserParse Settings',
            'InpUserParser',
            'manage_options',
            'inpuserparser',
            ['InpUserParser\Settings', 'displayPage']
        );
    }

//    Display Settings Page
    public static function displayPage()
    {
        // check if user is allowed access
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()) . ''; ?></h1>
            <a href="<?php echo get_bloginfo('url') . '/?' .self::SETTINGS_PAGE;?>"><?php
                echo esc_html__('Preview InpuserParser Page', 'inpuserparser')?></a>
            <form action="options.php" method="post">

                <?php

                // output security fields
                settings_fields(self::OPTION_NAME);

                // output setting sections
                do_settings_sections(self::SETTINGS_PAGE);

                // submit button
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /*
     * All settings Creation Functionalities
     * The Different Settings Options are dynamically Created from a set of predefined fields from User Class
     * */
    public static function registerSettings()
    {
        register_setting(
            self::OPTION_NAME,
            self::OPTION_NAME,
            ['InpUserParser\Settings', 'validateOptions']
        );

        add_settings_section(
            self::COLUMN_SECTION_ID,
            esc_html__('Customize Visible Columns', 'inpuserparser'),
            ['InpUserParser\Settings', 'columnSettingsCallback'],
            self::SETTINGS_PAGE
        );

        add_settings_section(
            self::SEARCH_SECTION_ID,
            esc_html__('Customize Visible Search Parameters', 'inpuserparser'),
            ['InpUserParser\Settings', 'searchSettingsCallback'],
            self::SETTINGS_PAGE
        );

        self::generateFields();
    }

//    Dynamically generate Settings Fields
    public static function generateFields()
    {
        // Add Search Visibility Field
        add_settings_field(
            'search_settings_visible',
            esc_html__('Enable Search Feature', 'inpuserparser'),
            ['InpUserParser\Settings', 'generateRadioField'],
            self::SETTINGS_PAGE,
            self::SEARCH_SECTION_ID,
            [ 'id' => 'search_settings_visible', 'label' => 'Visible or not' ]
        );

        // Loops Through Used Fields and Generate Settings Fields for both Settings Section..
        foreach (User::usedFields() as $field) {
            // Generate Column Settings Check Fields
            add_settings_field(
                'column_settings_' . $field,
                self::ucFields($field) . esc_html__(' Column Visibility', 'inpuserparser'),
                ['InpUserParser\Settings', 'generateCheckField'],
                self::SETTINGS_PAGE,
                self::COLUMN_SECTION_ID,
                [   'id' => 'column_settings_' . $field,
                    'label' => esc_html__('Set ', 'inpuserparser') . self::ucFields($field) .
                        esc_html__(' Column Visibility', 'inpuserparser'),
                    'section' => self::COLUMN_SECTION_ID,
                ]
            );

            // Generate Search Settings Check Fields
            add_settings_field(
                'search_settings_' . $field,
                self::ucFields($field) . esc_html__('  Search Feature', 'inpuserparser'),
                ['InpUserParser\Settings', 'generateCheckField'],
                self::SETTINGS_PAGE,
                self::SEARCH_SECTION_ID,
                [
                    'id' => 'search_settings_' . $field,
                    'label' => esc_html__('Enable ', 'inpuserparser') . self::ucFields($field) .
                        esc_html__(' Column Search', 'inpuserparser'),
                    'section' => self::SEARCH_SECTION_ID,
                ]
            );
        }
    }

//    Returns VisibleSearchFields Mostly Used By InpUserPage Class and Request Class
    public static function visibleSearchFields() : array
    {
        $visibleSearches = [];
        $options = get_option(self::OPTION_NAME, self::defaultOptions());
        foreach (User::usedFields() as $field) {
            if ((int) $options['search_settings_' . $field] === 1) {
                $visibleSearches[] = $field;
            }
        }

        if ($options['search_settings_visible'] == 'disable') {
            return [];
        }
        return $visibleSearches;
    }

//    Returns VisibleColumnsFields Mostly Used By InpUserPage Class and Request Class
    public static function visibleColumns() : array
    {
        $visibleColumns = [];
        foreach (User::defaultColumns() as $column) {
            $visibleColumns[] = $column;
        }
        $options = get_option(self::OPTION_NAME, self::defaultOptions());

        foreach (User::usedFields() as $field) {
            if (in_array($field, $visibleColumns, true)) {
                continue;
            }
            if ((int) $options['column_settings_' . $field] === 1) {
                $visibleColumns[] = $field;
            }
        }
        return $visibleColumns;
    }

    public static function searchSettingsCallback()
    {
        echo esc_html__('Customize the search Options Visible to the Users', 'inpuserparser');
    }

    public static function columnSettingsCallback()
    {
        echo esc_html__('Customize the Columns that are visible to the Users', 'inpuserparser');
    }

//    Validate Submitted Request and Reset Default Values
    public static function validateOptions(array $input) : array
    {
        foreach (User::usedFields() as $field) {
            $input['column_settings_' . $field] = ((int) $input['column_settings_' . $field] ===  1 ? 1 : 0);
            $input['search_settings_' . $field] = ((int) $input['search_settings_' . $field] ===  1 ? 1 : 0);
        }

        if (!array_key_exists($input['search_settings_visible'], self::searchRadioOptions())) {
            $input['search_settings_visible'] = null;
        }
        $input['search_settings_visible'] = $input['search_settings_visible'] ?? null;

        foreach (User::defaultColumns() as $field) {
            $input['column_settings_' . $field] = 1;
        }
        return $input;
    }

//    Returns Default Options of Search Fields
    public static function defaultOptions() : array
    {
        $returnArray = [];
        foreach (User::usedFields() as $field) {
            $returnArray['search_settings_' . $field] = false;
            $returnArray['column_settings_' . $field] =
                in_array((string) $field, User::defaultColumns(), true) ? true : false;
        }
        $returnArray['search_settings_visible'] = 'disable';
        return $returnArray;
    }

    public static function searchRadioOptions() : array
    {
        return [
            'enable' => esc_html__('Enable Search', 'inpuserparser'),
            'disable' => esc_html__('Disable Search', 'nipuserparser'),
        ];
    }

//   Array of Whitelisted Tags and Attributes
    public static function allowedTags() : array
    {
        return [
            'input' => [
                'id' => [],
                'name' => [],
                'type' => [],
                'value' => [],
                'checked' => [],
                'disabled' => [],
            ],
            'label' => [
                'for' => [],
            ],
            'br' =>[],
        ];
    }

//    Generate Radio Fields for Settings Page
    public static function generateRadioField(array $args)
    {
        $options = get_option(self::OPTION_NAME, self::defaultOptions());
        $id = $args['id'] ?? '';

        $selectedOptions = isset($options[$id]) ? sanitize_text_field($options[$id]) : '';
        $radioOptions = self::searchRadioOptions();

        foreach ($radioOptions as $value => $label) {
            $checked = checked($selectedOptions === $value, true, false);
            $output = '<label>';
            $output.='<input name="'.self::OPTION_NAME.'['. $id .']" type="radio" value="' .
                $value .'"'. $checked .'> ';
            $output.= '<span>'. $label .'</span></label><br />';
            echo wp_kses($output, self::allowedTags());
        }
    }

//    Generate Check Fields for Settings Page
    public static function generateCheckField(array $args)
    {
//        get saved or default values
        $options = get_option(self::OPTION_NAME, self::defaultOptions());
        $id = $args['id'] ?? '';
        $label = $args['label'] ?? '';
        $checked = isset($options[$id]) ? checked($options[$id], 1, false) : '';

//        Disable Necessary columns checkbox so They cant be Edited By Admin
        $disabled = "";
        if ($args['section'] === self::COLUMN_SECTION_ID) {
            if (self::defaultOptions()[$id]) {
                $disabled = "disabled";
            }
        }

//         Disable all Search Checkbox if search_settings_visible is Disabled
        if ($args['section'] === self::SEARCH_SECTION_ID) {
            if ((string) $options['search_settings_visible'] === 'disable') {
                $disabled = "disabled";
            }
        }

//         Generate Check Box Output
        $output = '<input id="'.self::OPTION_NAME.'_'. $id .
            '" name="'.self::OPTION_NAME.'['. $id .']" type="checkbox" value="1"'.
            $checked . ' ' . $disabled . '> ';
        $output.= '<label for="'.self::OPTION_NAME.'_'. $id .'">'. $label .'</label>';
        echo wp_kses($output, self::allowedTags());
    }
}
