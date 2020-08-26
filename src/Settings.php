<?php declare(strict_types=1);
# -*- coding: utf-8 -*-
/*
 * This file is part of the InpUserParser Wordpress Plugin
 *
 * (c) DeRavenedWriter
 *
 */

namespace InpUserParser;

use Twig;

/**
 * Class Responsible for Settings Functionality of Plugin
 *
 * This Class handles all functionality of the Plugin Settings
 * This Functionality includes displaying settings page,
 * Validating and Saving Settings Form and more...
 *
 * @author  DeRavenedWriter <deravenedwriter@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
class Settings
{

    /**
     * @var string Contains inpuserparser constant
     */
    const SETTINGS_PAGE = 'inpuserparser';

    /**
     * @var string Contains Inpuserparser search settings Id
     */
    const SEARCH_SECTION_ID = 'inpuserparser_search_settings';

    /**
     * @var string Contains Inpuserparser column settings Id
     */
    const COLUMN_SECTION_ID = 'inpuserparser_column_settings';

    /**
     * @var string Contains Inpuserparser option name
     */
    const OPTION_NAME = 'inpuserparser_options';

    /**
     * @var array Contains array of allowed tags
     */
    const ALLOWED_TAGS = [
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
        'br' => [],
    ];

    /**
     * @var string Name of Settings Page Template
     */
    const SETTINGS_PAGE_TEMPLATE ='settings_page.twig.php';



    /**
     * Initializes all needed Hooks for the settings class

     * @return  void
     */
    public function init()
    {
        \add_action('admin_menu', [$this, 'addMenu']);
        \add_action('admin_init', [$this, 'registerSettings']);
        \add_filter(
            'plugin_action_links_inpuserparser/inpuserparser.php',
            [$this, 'addSettingsLink']
        );
    }

    /**
     * Returns a link to the InpuserParser Settings page
     *
     * @return  string
     */
    public function getSettingsLink(): string
    {
        $url = \esc_url(\add_query_arg(
            'page',
            self::SETTINGS_PAGE,
            \get_admin_url() . 'admin.php'
        ));
        // Create the link.
        return "<a href='$url'>Settings</a>";
    }


    /**
     * Add InpuserParser Settings Links to array of action links
     *
     * @param array $links          Array Of Links
     *
     * @return  array
     */
    public function addSettingsLink(array $links): array
    {
        // Adds the link to the end of the array.
        \array_push(
            $links,
            $this->getSettingsLink()
        );
        return $links;
    }


    /**
     * Performs Uninstall Actions
     *
     * This Actions Includes removing transients and options
     *
     * @return  void
     */
    public function uninstall()
    {
        \delete_option(self::OPTION_NAME);
        \delete_transient(UserGenerator::USERS_TRANSIENT);
    }


    /**
     * Formats and Change first character to uppercase
     *
     * @param string $field          String to work on
     *
     * @return  string
     */
    public function ucFields(string $field): string
    {
        return Helpers::ucFields($field);
    }


    /**
     * Adds InpuserParser Settings Page
     *
     * @return  void
     */
    public function addMenu()
    {
        \add_submenu_page(
            'options-general.php',
            'InpUserParser Settings',
            'InpUserParser',
            'manage_options',
            'inpuserparser',
            [$this, 'displayPage']
        );
    }


    /**
     * Returns a ready instance of the Twig Environment with templates Dir Loaded
     *
     * @return  Twig\Environment
     */
    public function templateEngine() : Twig\Environment
    {
        $loader = new Twig\Loader\FilesystemLoader(__DIR__.'/templates');
        return new Twig\Environment($loader);
    }


    /**
     * Returns the Generated Settings Page
     *
     * @throws InpUserParserException
     * @return  string
     */
    public function generatePage()
    {
        $settingsPage = (new class()
        {
            public $adminPageTitle;
            public $previewUrl;
            public $previewUrlText;

            public function __construct()
            {
                $this->adminPageTitle = \esc_html(\get_admin_page_title());
                $this->previewUrl = \get_bloginfo('url') . '/?' . Settings::SETTINGS_PAGE;
                $this->previewUrlText = \esc_html__('Preview InpuserParser Page', 'inpuserparser');
            }
            public function settingsField()
            {
                \settings_fields(Settings::OPTION_NAME);
            }
            public function settingsSection()
            {
                \do_settings_sections(Settings::SETTINGS_PAGE);
            }
            public function submitButton()
            {
                \submit_button();
            }

        });

        try {
            return $this->templateEngine()->render(
                self::SETTINGS_PAGE_TEMPLATE,
                ['settingsPage' => $settingsPage]
            );
        } catch (Twig\Error\Error $e) {
            throw new InpUserParserException("Failed Generating Settings Page with Error: " . $e->getMessage());
        }
    }

    /**
     * Echos Out the Generated Settings Page
     *
     * @throws InpUserParserException
     * @return  void
     */
    public function displayPage()
    {
        if (!\current_user_can('manage_options')) {
            return;
        }
        echo $this->generatePage();
    }

    /**
     * Registers Settings, Settings Sections and Fields
     *
     * @return  void
     */
    public function registerSettings()
    {
        \register_setting(
            self::OPTION_NAME,
            self::OPTION_NAME,
            [$this, 'validateOptions']
        );

        \add_settings_section(
            self::COLUMN_SECTION_ID,
            \esc_html__('Customize Visible Columns', 'inpuserparser'),
            [$this, 'columnSettingsCallback'],
            self::SETTINGS_PAGE
        );

        \add_settings_section(
            self::SEARCH_SECTION_ID,
            \esc_html__('Customize Visible Search Parameters', 'inpuserparser'),
            [$this, 'searchSettingsCallback'],
            self::SETTINGS_PAGE
        );

        $this->generateFields();
    }

    /**
     * Generates and Registers Settings Fields
     *
     * @return  void
     */
    public function generateFields()
    {
        // Add Search Visibility Field
        \add_settings_field(
            'search_settings_visible',
            \esc_html__('Enable Search Feature', 'inpuserparser'),
            [$this, 'outputRadioField'],
            self::SETTINGS_PAGE,
            self::SEARCH_SECTION_ID,
            ['id' => 'search_settings_visible', 'label' => 'Visible or not']
        );

        // Loops Through Used Fields and Generate Settings Fields for both Settings Section..
        foreach (User::USED_FIELDS as $field) {
            // Generate Column Settings Check Fields
            \add_settings_field(
                'column_settings_' . $field,
                $this->ucFields($field) . \esc_html__(' Column Visibility', 'inpuserparser'),
                [$this, 'outputCheckField'],
                self::SETTINGS_PAGE,
                self::COLUMN_SECTION_ID,
                ['id' => 'column_settings_' . $field,
                    'label' => \esc_html__('Set ', 'inpuserparser') . $this->ucFields($field) .
                        \esc_html__(' Column Visibility', 'inpuserparser'),
                    'section' => self::COLUMN_SECTION_ID,
                ]
            );

            // Generate Search Settings Check Fields
            \add_settings_field(
                'search_settings_' . $field,
                $this->ucFields($field) . \esc_html__('  Search Feature', 'inpuserparser'),
                [$this, 'outputCheckField'],
                self::SETTINGS_PAGE,
                self::SEARCH_SECTION_ID,
                [
                    'id' => 'search_settings_' . $field,
                    'label' => \esc_html__('Enable ', 'inpuserparser') . $this->ucFields($field) .
                        \esc_html__(' Column Search', 'inpuserparser'),
                    'section' => self::SEARCH_SECTION_ID,
                ]
            );
        }
    }


    /**
     * Generates and Returns Default Settings Options Value
     *
     * @return  array
     */
    public function defaultOptions(): array
    {
        $returnArray = [];
        foreach (User::USED_FIELDS as $field) {
            $returnArray['search_settings_' . $field] = false;
            $returnArray['column_settings_' . $field] =
                \in_array((string)$field, User::DEFAULT_COLUMNS, true);
        }

        $returnArray['search_settings_visible'] = 'disable';
        return $returnArray;
    }


    /**
     * Returns an array of the Search Fields that are enabled to be visible
     *
     * @return  array
     */
    public function visibleSearchFields(): array
    {
        $visibleSearches = [];
        $options = \get_option(self::OPTION_NAME, $this->defaultOptions());
        foreach (User::USED_FIELDS as $field) {
            if (isset($options['search_settings_' . $field]) && (int)$options['search_settings_' . $field] === 1) {
                $visibleSearches[] = $field;
            }
        }

        if ($options['search_settings_visible'] == 'disable') {
            return [];
        }
        return $visibleSearches;
    }

    /**
     * Returns an array of the Column Fields that are enabled to be visible
     *
     * @return  string[]
     */
    public function visibleColumns(): array
    {
        $visibleColumns = [];
        foreach (User::DEFAULT_COLUMNS as $column) {
            $visibleColumns[] = $column;
        }
        $options = \get_option(self::OPTION_NAME, $this->defaultOptions());

        foreach (User::USED_FIELDS as $field) {
            if (\in_array($field, $visibleColumns, true)) {
                continue;
            }
            if (isset($options['column_settings_' . $field]) && (int)$options['column_settings_' . $field] === 1) {
                $visibleColumns[] = $field;
            }
        }
        return $visibleColumns;
    }


    /**
     * Echoes out content for Search Settings Section
     *
     * @return  void
     */
    public function searchSettingsCallback()
    {
        echo \esc_html__('Customize the search Options Visible to the Users', 'inpuserparser');
    }


    /**
     * Echoes out content for Column Settings Section
     *
     * @return  void
     */
    public function columnSettingsCallback()
    {
        echo \esc_html__('Customize the Columns that are visible to the Users', 'inpuserparser');
    }


    /**
     * Validate and Returns the Submitted Settings Form
     *
     * @param array $input      Contains Values of Submitted Form
     *
     * @return  array
     */
    public function validateOptions(array $input): array
    {
        foreach (User::USED_FIELDS as $field) {
            $input['column_settings_' . $field] = $input['column_settings_' . $field] ?? null;
            $input['search_settings_' . $field] = $input['search_settings_' . $field] ?? null;
            $input['column_settings_' . $field] = ((int)$input['column_settings_' . $field] === 1 ? 1 : 0);
            $input['search_settings_' . $field] = ((int)$input['search_settings_' . $field] === 1 ? 1 : 0);
        }

        $input['search_settings_visible'] = $input['search_settings_visible'] ?? null;

        if (!\array_key_exists($input['search_settings_visible'], $this->searchRadioOptions())) {
            $input['search_settings_visible'] = null;
        }


        foreach (User::DEFAULT_COLUMNS as $field) {
            $input['column_settings_' . $field] = 1;
        }
        return $input;
    }

    /**
     * Returns HTML options for Radio Field
     *
     * @return  array
     */
    public function searchRadioOptions(): array
    {
        return [
            'enable' => \esc_html__('Enable Search', 'inpuserparser'),
            'disable' => \esc_html__('Disable Search', 'nipuserparser'),
        ];
    }


    /**
     * Echoes Out Radio Field
     *
     * @param array $args          Contains arguments for Generating radio field
     *
     * @return  void
     */
    public function outputRadioField(array $args)
    {
        echo $this->generateRadioField($args);
    }

    /**
     * Echoes Out Check Field
     *
     * @param array $args          Contains arguments for Generating check field
     *
     * @return  void
     */
    public function outputCheckField(array $args)
    {
        echo $this->generateCheckField($args);
    }


    /**
     * Generates and returns Radio Field with the given required args
     *
     * @param array $args          Contains arguments for Generating radio field
     *
     * @return  string
     */
    public function generateRadioField(array $args) : string
    {
        $options = \get_option(self::OPTION_NAME, $this->defaultOptions());
        $id = $args['id'] ?? '';

        $selectedOptions = isset($options[$id]) ? \sanitize_text_field($options[$id]) : '';
        $radioOptions = $this->searchRadioOptions();

        $output = '';
        foreach ($radioOptions as $value => $label) {
            $checked = \checked($selectedOptions === $value, true, false);
            $field = '<label>';
            $field .= '<input name="' . self::OPTION_NAME . '[' . $id . ']" type="radio" value="' .
                $value . '"' . $checked . '> ';
            $field .= '<span>' . $label . '</span></label><br />';
            $output .= \wp_kses($field, self::ALLOWED_TAGS);
        }
        return $output;
    }

    /**
     * Generates and returns Check Field with the given required args
     *
     * @param array $args          Contains arguments for Generating check field
     *
     * @return  string
     */
    public function generateCheckField(array $args) : string
    {
        //get saved or default values
        $options = \get_option(self::OPTION_NAME, $this->defaultOptions());
        $id = $args['id'] ?? '';
        $label = $args['label'] ?? '';
        $checked = isset($options[$id]) ? \checked($options[$id], 1, false) : '';

        //Disable Necessary columns checkbox so They can't be Edited By Admin
        $disabled = "";
        if ($args['section'] === self::COLUMN_SECTION_ID) {
            if ($this->defaultOptions()[$id]) {
                $disabled = "disabled";
            }
        }

        // Disable all Search Checkbox if search_settings_visible is Disabled
        if ($args['section'] === self::SEARCH_SECTION_ID) {
            if ((string)$options['search_settings_visible'] === 'disable') {
                $disabled = "disabled";
            }
        }

        //Generate Check Box Output
        $output = '<input id="' . self::OPTION_NAME . '_' . $id .
            '" name="' . self::OPTION_NAME . '[' . $id . ']" type="checkbox" value="1"' .
            $checked . ' ' . $disabled . '> ';
        $output .= '<label for="' . self::OPTION_NAME . '_' . $id . '">' . $label . '</label>';
        return \wp_kses($output, self::ALLOWED_TAGS);
    }
}
