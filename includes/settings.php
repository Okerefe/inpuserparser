<?php declare(strict_types = 1);

namespace InpUserParser;

class Settings
{

    /*
     * Name
     * username
     * email
     * street address
     * phone
     * website
     * company name
     *
     */
    const SETTINGS_PAGE = 'inpuserparser';
    const SEARCH_SECTION_ID = 'inpuserparser_search_settings';
    const COLUMN_SECTION_ID = 'inpuserparser_column_settings';
    const OPTION_NAME = 'inpuserparser_options';

    public static function init()
    {
        add_action('admin_menu', ['InpUserParser\Settings', 'addMenu']);
        add_action('admin_init', ['InpUserParser\Settings', 'registerSettings']);
    }

    public static function install()
    {
    }

    public static function uninstall()
    {
        delete_option(self::OPTION_NAME);
    }

    public static function ucFields(string $field): string
    {
        return ucwords(preg_replace('/(?<!\ )[A-Z]/', ' $0', $field));
    }

    public static function defaultColumns() : array
    {
        return[
                'id',
                'name',
                'username',
                ];
    }

    public static function usedFields() : array
    {
            return[
                'id',
                'name',
                'username',
                'email',
                'streetAddress',
                'phone',
                'website',
                'companyName',
            ];
    }

    public static function addMenu()
    {
        /*
         * Add Plugin to to subMenu
         */
        add_submenu_page(
            'options-general.php',
            'InpUserParse Settings',
            'InpUserParser',
            'manage_options',
            'inpuserparser',
            ['InpUserParser\Settings', 'displayPage']
        );
    }

    public static function displayPage()
    {
        // check if user is allowed access
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">

                <?php

                // output security fields
                settings_fields(self::OPTION_NAME);

                // output setting sections
                do_settings_sections('inpuserparser');

                // submit button
                submit_button();

                ?>

            </form>
        </div>

        <?php
    }

    /*
     * All settings Creation Functionalities
     * The Different Settings Options are dynamically Created from a set of predefined fields
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
            'Customize Visible Columns',
            ['InpUserParser\Settings', 'columnSettingsCallback'],
            self::SETTINGS_PAGE
        );

        add_settings_section(
            self::SEARCH_SECTION_ID,
            'Customize Visible Search Parameters',
            ['InpUserParser\Settings', 'searchSettingsCallback'],
            self::SETTINGS_PAGE
        );

        self::generateFields();
    }

    public static function generateFields()
    {
        // Add Search Visibility Field
        add_settings_field(
            'search_settings_visible',
            'Enable Search Feature',
            ['InpUserParser\Settings', 'generateRadioField'],
            self::SETTINGS_PAGE,
            self::SEARCH_SECTION_ID,
            [ 'id' => 'search_settings_visible', 'label' => 'Visible or not' ]
        );

        // Loops Through Used Fields and Generate Settings Fields for both Settings Section..
        foreach (self::usedFields() as $field) {
            // Generate Column Settings Check Fields
            add_settings_field(
                'column_settings_' . $field,
                self::ucFields($field) . ' Column Visibility',
                ['InpUserParser\Settings', 'generateCheckField'],
                self::SETTINGS_PAGE,
                self::COLUMN_SECTION_ID,
                [   'id' => 'column_settings_' . $field,
                    'label' => 'Set ' . self::ucFields($field) . ' Column Visiblility',
                    'section' => self::COLUMN_SECTION_ID,
                ]
            );

            // Generate Search Settings Check Fields
            add_settings_field(
                'search_settings_' . $field,
                self::ucFields($field) . ' Search Feature',
                ['InpUserParser\Settings', 'generateCheckField'],
                self::SETTINGS_PAGE,
                self::SEARCH_SECTION_ID,
                [
                    'id' => 'search_settings_' . $field,
                    'label' => 'Enable ' . self::ucFields($field) . ' Column Search',
                    'section' => self::SEARCH_SECTION_ID,
                ]
            );
        }
    }

    public static function searchSettingsCallback()
    {
        echo "Customize the search Options Visible to the Users";
    }

    public static function columnSettingsCallback()
    {
        echo "Customize the Columns that are visible to the Users";
    }

//    Validate Submitted Request and Reset Default Values
    public static function validateOptions(array $input) : array
    {
        foreach (self::usedFields() as $field) {
            $input['column_settings_' . $field] = ((int) $input['column_settings_' . $field] === 1 ? 1 : 0);
            $input['search_settings_' . $field] = ((int) $input['search_settings_' . $field] === 1 ? 1 : 0);
        }
        foreach (self::defaultColumns() as $field) {
            $input['column_settings_' . $field] = 1;
        }

        if (!array_key_exists($input['search_settings_visible'], self::searchRadioOptions())) {
            $input['search_settings_visible'] = null;
        }
        $input['search_settings_visible'] = $input['search_settings_visible'] ?? null;

        return $input;
    }

    public static function defaultOptions() : array
    {
        $returnArray = [];
        foreach (self::usedFields() as $field) {
            $returnArray['search_settings_' . $field] = false;
            $returnArray['column_settings_' . $field] =
                in_array((string) $field, self::defaultColumns(), true) ? true : false;
        }
        $returnArray['search_settings_visible'] = 'enable';
        return $returnArray;
    }

    public static function searchRadioOptions() : array
    {
        return [
            'enable' => 'Enable Search',
            'disable' => 'Disable Search',
        ];
    }

    public static function allowedTags() : array
    {
//        Array of Whitelisted Tags and Attributes
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

    public static function generateRadioField(array $args)
    {
        $options = get_option(self::OPTION_NAME, self::defaultOptions());
        $id = $args['id'] ?? '';
        $label = $args['label'] ?? '';

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

    public static function generateCheckField(array $args)
    {
        $options = get_option(self::OPTION_NAME, self::defaultOptions());
        $id = $args['id'] ?? '';
        $label = $args['label'] ?? '';
        $checked = isset($options[$id]) ? checked($options[$id], 1, false) : '';

        $disabled = "";
        if ($args['section'] === self::COLUMN_SECTION_ID) {
            if (self::defaultOptions()[$id]) {
                $disabled = "disabled";
            }
        }

        $output = '<input id="'.self::OPTION_NAME.'_'. $id .
            '" name="'.self::OPTION_NAME.'['. $id .']" type="checkbox" value="1"'.
            $checked . ' ' . $disabled . '> ';
        $output.= '<label for="'.self::OPTION_NAME.'_'. $id .'">'. $label .'</label>';
        echo wp_kses($output, self::allowedTags());
    }
}
