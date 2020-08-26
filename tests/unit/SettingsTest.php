<?php declare(strict_types=1);
# -*- coding: utf-8 -*-
/*
 * This file is part of the InpUserParser Wordpress Plugin
 *
 * (c) DeRavenedWriter
 *
 */

namespace InpUserParser;

use Brain\Monkey\Functions;
use Mockery;

/**
 * @author  DeRavenedWriter <deravenedwriter@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
final class SettingsTest extends InpUserParserTest
{


    /** @test */
    public function ifInitWorks()
    {
        (new Settings())->init();
        self::assertTrue(has_action('admin_menu', 'InpUserParser\Settings->addMenu()'));
        self::assertTrue(has_action('admin_init', 'InpUserParser\Settings->registerSettings()'));
        self::assertTrue(has_filter(
            'plugin_action_links_inpuserparser/inpuserparser.php',
            'InpUserParser\Settings->addSettingsLink()'
        ));
    }



    /** @test */
    public function ifGetSettingsLinkWorks()
    {
        Functions\expect('get_admin_url')
            ->once()
            ->andReturn('someurl');

        Functions\expect('add_query_arg')
            ->once()
            ->with('page', Settings::SETTINGS_PAGE, 'someurladmin.php')
            ->andReturn('testpassed');

        Functions\expect('esc_url')
            ->once()
            ->with('testpassed')
            ->andReturn('testhaspassed');

        $this->assertStringContainsString('testhaspassed', (new Settings())->getSettingsLink());
    }

    /** @test */
    public function ifAddSettingsLinkWorks()
    {
        $settings = $this->getMockBuilder(Settings::class)
            ->onlyMethods(['getSettingsLink'])
            ->getMock();

        $settings->expects($this->once())
            ->method('getSettingsLink')
            ->willReturn('somesettings');

        $this->assertSame(['somesettings'], $settings->addSettingsLink([]));
    }

    /** @test */
    public function ifUninstallWOrks()
    {
        Functions\expect('delete_option')
            ->once()
            ->with(Settings::OPTION_NAME);

        Functions\expect('delete_transient')
            ->once()
            ->with(UserGenerator::USERS_TRANSIENT);

        (new Settings())->uninstall();
    }

    /** @test */
    public function ifAddMenuWorks()
    {
        Functions\expect('add_submenu_page')
            ->once()
            ->with(
                'options-general.php',
                'InpUserParser Settings',
                'InpUserParser',
                'manage_options',
                'inpuserparser',
                Mockery::type('array')
            );

        (new Settings())->addMenu();
    }


    /** @test */
    public function ifRegisterSettingsWorks()
    {
        Functions\expect('register_setting')
            ->once()
            ->with(
                Settings::OPTION_NAME,
                Settings::OPTION_NAME,
                Mockery::type('array')
            );

        Functions\expect('add_settings_section')
            ->times(2);

        Functions\expect('esc_html__')
            ->times(2);

        $settings = $this->getMockBuilder(Settings::class)
            ->onlyMethods(['generateFields'])
            ->getMock();

        $settings->expects($this->once())
            ->method('generateFields');

        $settings->registerSettings();
    }


    /** @test */
    public function ifGenerateFieldsWorks()
    {

        Functions\expect('add_settings_field')
            ->times(((count(User::USED_FIELDS)  * 2) + 1));

        Functions\expect('esc_html__')
            ->times(((count(User::USED_FIELDS)  * 6) + 1));

        $settings = $this->getMockBuilder(Settings::class)
            ->onlyMethods(['ucFields'])
            ->getMock();

        $settings->expects($this->exactly((count(User::USED_FIELDS)  * 4)))
            ->method('ucFields');

        $settings->generateFields();
    }

    /** @test */
    public function ifDefaultOptionsWorks()
    {
        foreach (User::USED_FIELDS as $field) {
            $this->assertArrayHasKey("search_settings_{$field}", (new Settings())->defaultOptions());
        }
        $this->assertArrayHasKey('search_settings_visible', (new Settings())->defaultOptions());
        $this->assertSame('disable', (new Settings())->defaultOptions()['search_settings_visible']);
    }



    /** @test */
    public function ifVisibleSearchFieldsReturnsValue()
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn([
                'search_settings_name' => 1,
                'search_settings_username' => 0,
                'search_settings_visible' => 'enable',
            ]);

        $settings = $this->getMockBuilder(Settings::class)
            ->onlyMethods(['defaultOptions'])
            ->getMock();

        $settings->expects($this->once())
            ->method('defaultOptions');


        $visibleSearchColumns = $settings->visibleSearchFields();
        $this->assertContains('name', $visibleSearchColumns);
        $this->assertSame(1, count($visibleSearchColumns));
    }

    /** @test */
    public function ifVisibleSearchFieldsReturnsEmptyCauseOfDisabledSearch()
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn([
                'search_settings_name' => 1,
                'search_settings_username' => 0,
                'search_settings_visible' => 'disable',
            ]);

        $settings = $this->getMockBuilder(Settings::class)
            ->onlyMethods(['defaultOptions'])
            ->getMock();

        $settings->expects($this->once())
            ->method('defaultOptions');

        $this->assertSame([], $settings->visibleSearchFields());
    }

    /** @test */
    public function ifVisibleSearchFieldsReturnsEmptyCauseOfNoVisibleField()
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn([
                'search_settings_name' => 0,
                'search_settings_username' => 0,
                'search_settings_visible' => 'enable',
            ]);

        $settings = $this->getMockBuilder(Settings::class)
            ->onlyMethods(['defaultOptions'])
            ->getMock();

        $settings->expects($this->once())
            ->method('defaultOptions');

        $this->assertSame([], $settings->visibleSearchFields());
    }


    /** @test */
    public function ifVisibleColumnsReturnsDefaults()
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn([]);

        $settings = $this->getMockBuilder(Settings::class)
            ->onlyMethods(['defaultOptions'])
            ->getMock();

        $settings->expects($this->once())
            ->method('defaultOptions');

        $visibleColumns = $settings->visibleColumns();
        $this->assertSame(3, count($visibleColumns));
        $this->assertContains('id', $visibleColumns);
        $this->assertContains('name', $visibleColumns);
        $this->assertContains('username', $visibleColumns);
    }

    /** @test */
    public function ifVisibleColumnsReturnsFromGetOptions()
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn([
                'column_settings_companyName' => 1,
            ]);

        $settings = $this->getMockBuilder(Settings::class)
            ->onlyMethods(['defaultOptions'])
            ->getMock();

        $settings->expects($this->once())
            ->method('defaultOptions');


        $visibleColumns = $settings->visibleColumns();
        $this->assertSame(4, count($visibleColumns));
        $this->assertContains('companyName', $visibleColumns);
    }

    /** @test */
    public function ifSearchSettingsCallbackWorks()
    {
        Functions\expect('esc_html__')
            ->once()
            ->andReturn('Hey There, am a TEST');

        (new Settings())->searchSettingsCallback();
    }

    /** @test */
    public function ifColumnSettingsCallbackWorks()
    {
        Functions\expect('esc_html__')
            ->once()
            ->andReturn('Hey There, am a TEST');

        (new Settings())->columnSettingsCallback();
    }


    /** @test */
    public function ifValidateOptionsWorks()
    {
        $settings = $this->getMockBuilder(Settings::class)
            ->onlyMethods(['searchRadioOptions'])
            ->getMock();

        $settings->expects($this->once())
            ->method('searchRadioOptions')
            ->willReturn([]);


        $input = $settings->validateOptions([
            'column_settings_companyName' => 1,
            'search_settings_name' => 0,
        ]);

        $this->assertSame(1, $input['column_settings_companyName']);
        $this->assertSame(0, $input['search_settings_name']);
        $this->assertSame(1, $input['column_settings_name']);
        $this->assertSame(null, $input['search_settings_visible']);
    }


    /** @test */
    public function ifSearchRadioOptionWorks()
    {
        Functions\expect('esc_html__')
            ->times(2)
            ->andReturn('Hey There, am a TEST');

        $searchOptions = (new Settings())->searchRadioOptions();
        $this->assertArrayHasKey('enable', $searchOptions);
        $this->assertArrayHasKey('disable', $searchOptions);
    }

    /** @test */
    public function ifGenerateRadioFieldWorks()
    {

        Functions\stubs(
            [
                'sanitize_text_field',
                'wp_kses',

                'checked' => 'checked',
                'get_option' => ['search_settings_visible' => 1],

            ]
        );

        $settings = $this->getMockBuilder(Settings::class)
            ->onlyMethods(['defaultOptions', 'searchRadioOptions'])
            ->getMock();

        $settings->expects($this->once())
            ->method('defaultOptions');


        $settings->expects($this->once())
            ->method('searchRadioOptions')
            ->willReturn([
                'enable' => 'someenablement',
                'disable' => 'somedisenablement',
            ]);

        $radioField = $settings->generateRadioField(['id' => 'search_settings_visible', 'label' => 'Visible or not']);
        $this->assertStringContainsString('search_settings_visible', $radioField);
        $this->assertStringContainsString('checked', $radioField);
    }


    /** @test */
    public function ifGenerateCheckFieldWorks()
    {

        Functions\when('wp_kses')->returnArg();
        Functions\when('checked')->justReturn('checked');
        Functions\when('get_option')->justReturn(['column_settings_companyName' => 1]);


        $settings = $this->getMockBuilder(Settings::class)
            ->onlyMethods(['defaultOptions'])
            ->getMock();

        $settings->expects($this->exactly(2))
            ->method('defaultOptions')
            ->willReturn(['column_settings_companyName' => 1]);


        $radioField = $settings->generateCheckField(
            [
                'id' => 'column_settings_companyName',
                'label' => 'Visible or not',
                'section' => 'inpuserparser_column_settings',
            ]
        );

        $this->assertStringContainsString('column_settings_companyName', $radioField);
        $this->assertStringContainsString('checked', $radioField);
    }

    /** @test */
    public function ifGeneratePageWorks()
    {

        Functions\stubs(
            [
                'esc_html',
                'esc_html__',
                'get_bloginfo',

                'get_admin_page_title'  => "testing_something",
            ]
        );

        $twigEnviron = $this->getMockBuilder(\Twig\Environment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        $settings = $this->getMockBuilder(Settings::class)
            ->onlyMethods(['templateEngine'])
            ->getMock();


        $twigEnviron->expects($this->once())
            ->method('render')
            ->willReturn("just_testing_something");

        $settings->expects($this->once())
            ->method('templateEngine')
            ->willReturn($twigEnviron);

        $this->assertSame('just_testing_something', $settings->generatePage());
    }



    /** @test
     * Integration Test for GenerateTables Interaction with the Template Engine
     * We ought to see that there is no bug in the templates..
     */
    public function ifSettingsPageTemplateIsBugFree()
    {

        Functions\stubs(
            [
                'esc_html',
                'esc_html__',
                'get_bloginfo',
                'get_admin_page_title'  => "admin_page_title_present",
            ]
        );

        Functions\expect('settings_fields')
            ->once();

        Functions\expect('do_settings_sections')
            ->once();

        Functions\expect('submit_button')
            ->once();

        $settingsPage = (new Settings())->generatePage();

        $this->assertStringContainsString('Preview InpuserParser Page', $settingsPage);
        $this->assertStringContainsString('admin_page_title_present', $settingsPage);
    }

}