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
 * @author  DeRavenedWriter <okerefe@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
final class InpUserParserPageTest extends InpUserParserTest
{

    //.................. Data Providers

    public function dataForBuildUpFunc()
    {
        return [
            ['scriptUrl'],
            ['styleUrl'],
            ['nonce'],
            ['ajaxUrl'],
            ['viewSearchText'],
            ['canManageOptions'],
            ['settingsText'],
            ['canManageOptions'],
            ['settingsText'],
            ['searchByText'],
        ];
    }


    public function dataForTwigTemplate()
    {
        return [
            ['somescripturl'],
            ['somestyleurl'],
            ['somenonce'],
            ['someajaxurl'],
            ['someheading'],
            ['someviewsearchtext'],
            ['somesettingstext'],
            ['somesearchbytext'],
            ['field1'],
            ['field2'],
            ['field3'],
        ];
    }


    //.................. End of Data Providers



    /** @test */
    public function ifInitWorks()
    {
        (new InpUserParserPage())->init();
        self::assertNotFalse(has_action('init', 'InpUserParser\InpUserParserPage->addRewriteRule()'));
        self::assertNotFalse(has_action('plugins_loaded', 'InpUserParser\InpUserParserPage->loadTextDomain()'));
        self::assertNotFalse(has_action('parse_request', 'InpUserParser\InpUserParserPage->parseRequest()'));
        self::assertNotFalse(has_filter('query_vars', 'InpUserParser\InpUserParserPage->inputQueryVars()'));
    }

    /** @test */
    public function ifTextDomainIsLoaded()
    {


        Functions\expect('load_plugin_textdomain')
            ->once();

        Functions\expect('plugin_dir_path')
            ->once();

        (new InpUserParserPage())->loadTextDomain();
    }


    /** @test */
    public function ifRewriteRuleAdded()
    {
        Functions\expect('add_rewrite_rule')
            ->once();

        (new InpUserParserPage())->addRewriteRule();
    }


    /** @test */
    public function ifQueryVarInputed()
    {
        $this->assertSame(['inpuserparser'], (new InpUserParserPage())->inputQueryVars([]));
    }

    /** @test */
    public function ifRequestParsed()
    {
        $inpUserParserPage = $this->getMockBuilder(InpUserParserPage::class)
            ->onlyMethods(['loadPage', 'endRequest'])
            ->getMock();

        $inpUserParserPage->expects($this->once())
            ->method('loadPage');

        $inpUserParserPage->expects($this->once())
            ->method('endRequest');

        $wp = (new class {
            public $query_vars = ['inpuserparser' => 'somethings'];
        });

        $inpUserParserPage->parseRequest($wp);
    }


    /** @test */
    public function ifLoadPageWorks()
    {
        $inpUserParserPage = $this->getMockBuilder(InpUserParserPage::class)
            ->onlyMethods(['generatePage'])
            ->getMock();

        $inpUserParserPage->expects($this->once())
            ->method('generatePage')
            ->willReturn('Hey am Just a Test');

        $inpUserParserPage->loadPage();
    }

    /** @test */
    public function ifGeneratePageWorks()
    {
        $twigEnviron = $this->getMockBuilder(\Twig\Environment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        $inpUserParserPage = $this->getMockBuilder(InpUserParserPage::class)
            ->onlyMethods(['templateEngine', 'buildUp'])
            ->getMock();

            $twigEnviron->expects($this->once())
            ->method('render')
            ->with(InpUserParserPage::PAGE_TEMPLATE, ['page' => $inpUserParserPage])
            ->willReturn("somepagehere");

        $inpUserParserPage->expects($this->once())
            ->method('templateEngine')
            ->willReturn($twigEnviron);

        $inpUserParserPage->expects($this->once())
            ->method('buildUp');

        $this->assertSame('somepagehere', $inpUserParserPage->generatePage());
    }


    /** @test
     * @dataProvider dataForBuildUpFunc
     */
    public function ifBuildUpWorks($property)
    {

        Functions\expect('esc_url')
            ->times(3)
            ->andReturn('just testing stuffs');

        Functions\expect('esc_attr')
            ->once()
            ->andReturn('just testing stuffs');

        Functions\expect('esc_html__')
            ->times(4)
            ->andReturn('just testing stuffs');

        Functions\expect('plugins_url')
            ->times(2)
            ->with(Mockery::type('string'), Mockery::type('string'))
            ->andReturn('just testing stuffs');

        Functions\expect('wp_create_nonce')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturn('just testing stuffs');

        Functions\expect('admin_url')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturn('just testing stuffs');

        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn('just testing stuffs');

        $inpUserParserPage = $this->getMockBuilder(InpUserParserPage::class)
            ->onlyMethods(['settingsLink', 'searchFields'])
            ->getMock();

        $inpUserParserPage->expects($this->once())
            ->method('searchFields')
            ->willReturn([]);

        $inpUserParserPage->expects($this->once())
            ->method('settingsLink')
            ->willReturn('just testing stuffs');

        $inpUserParserPage->buildUp();

        $this->assertStringContainsString('just testing stuffs', $inpUserParserPage->$property);
    }


    /** @test
     * @dataProvider dataForTwigTemplate
     *
     * Integration test to check if there is any error in the twig template
     */
    public function ifTwigRendersWellThroughGeneratePage($value)
    {
        $inpUserParserPage = $this->getMockBuilder(InpUserParserPage::class)
            ->onlyMethods(['buildUp'])
            ->getMock();

        $inpUserParserPage->scriptUrl =  'somescripturl';
        $inpUserParserPage->styleUrl = 'somestyleurl';
        $inpUserParserPage->nonce = 'somenonce';
        $inpUserParserPage->ajaxUrl = 'someajaxurl';
        $inpUserParserPage->heading = 'someheading';
        $inpUserParserPage->viewSearchText = 'someviewsearchtext';
        $inpUserParserPage->canManageOptions = true;

        //settings from InpUserParser\Settings::getSettingsLink()
        $inpUserParserPage->settingsText = 'somesettingstext';

        $inpUserParserPage->searchByText = 'somesearchbytext';
        $inpUserParserPage->searchFields = ['field1','field2', 'field3'];
        $ucCase = Helpers::ucFields('field1'); //Check if the Uppercase Function is not Broken
        $inpUserParserPage->isSearchFields = true;

        $inpUserParserPage->expects($this->once())
            ->method('buildUp');

        $page = $inpUserParserPage->generatePage();
        $this->assertStringContainsString($ucCase, $page);
        $this->assertStringContainsString($value, $page);
    }


}
