<?php

class AliasesTest extends WP_UnitTestCase
{
    protected $atts;
    protected $content;
    protected $tag;

    function setUp()
    {
        parent::setUp();

        add_shortcode('test', function ($atts, $content, $tag) {
            $this->atts = $atts;
            $this->content = $content;
            $this->tag = $tag;
        });
    }

    /**
     * @test
     */
    function it_passes_the_same_attributes_to_the_target_shortcode()
    {
        add_shortcode_alias('test_alias', 'test');

        do_shortcode('[test_alias foo=bar]');

        $this->assertSame('test', $this->tag);

        $this->assertAttEquals('foo', 'bar');

        do_shortcode('[test_alias one=two three four]');

        $this->assertAttEquals('one', 'two');
        $this->assertAttEquals(0, 'three');
        $this->assertAttEquals(1, 'four');
    }

    /**
     * @test
     */
    public function it_can_pass_default_attribute_values()
    {
        add_shortcode_alias('test_alias_simple_defaults', 'test', [
            'atts' => [
                'a' => 'b',
                'apple' => 'orange'
            ]
        ]);

        do_shortcode('[test_alias_simple_defaults]');

        $this->assertAttEquals('a', 'b');
        $this->assertAttEquals('apple', 'orange');

        // defaults are overriden by passed attributes

        do_shortcode('[test_alias_simple_defaults a=z apple=juice]');

        $this->assertAttEquals('a', 'z');
        $this->assertAttEquals('apple', 'juice');

        add_shortcode_alias('test_alias_alt_defaults', 'test', [
            'atts' => [
                'a' => ['default' => 'Ardvark'],
                'b' => ['default' => 'Bear']
            ]
        ]);

        do_shortcode('[test_alias_alt_defaults]');

        $this->assertAttEquals('a', 'Ardvark');
        $this->assertAttEquals('b', 'Bear');

        do_shortcode('[test_alias_alt_defaults a=b b=c]');

        $this->assertAttEquals('a', 'b');
        $this->assertAttEquals('b', 'c');
    }

    /**
     * @test
     */
    public function it_can_set_the_default_content_for_the_shortcode()
    {
        add_shortcode_alias('test_alias_default_content', 'test', [
            'content' => 'foo'
        ]);

        do_shortcode('[test_alias_default_content]');

        $this->assertContentEquals('foo');

        do_shortcode('[test_alias_default_content]bar[/test_alias_default_content]');

        $this->assertContentEquals('bar'); // default overriden
    }

    /**
     * @test
     */
    public function it_can_prepend_something_to_an_attribute_value()
    {
        add_shortcode_alias('test_alias_prepend', 'test', [
            'atts' => [
                'name' => ['prepend' => 'Hi there, ']
            ]
        ]);

        do_shortcode('[test_alias_prepend name=Evan]');

        $this->assertAttEquals('name', 'Hi there, Evan');
    }

    /**
     * @test
     */
    public function it_can_append_something_to_an_attribute_value()
    {
        add_shortcode_alias('test_alias_append', 'test', [
            'atts' => [
                'plugin_is' => ['append' => '.. not.']
            ]
        ]);

        do_shortcode('[test_alias_append plugin_is=cool!]');

        $this->assertAttEquals('plugin_is', 'cool!.. not.');
    }

    /**
     * @test
     */
    public function it_can_override_attribute_passed_values()
    {
        add_shortcode_alias('test_alias_force', 'test', [
            'atts' => [
                'plugin_is' => ['override' => 'Totally badass.']
            ]
        ]);

        do_shortcode('[test_alias_force plugin_is=LAME]');

        $this->assertAttEquals('plugin_is', 'Totally badass.');
    }

    /**
     * @test
     */
    public function it_applies_filters_using_the_alias_name()
    {
        add_shortcode_alias('alias_tag', 'test');

        $actions = [];
        add_action('all', function () use (&$actions) {
            @$actions[ current_filter() ]++;
        });

        do_shortcode('[alias_tag]');

        $this->assertEquals(1, $actions['shortcode_alias/alias_tag/atts']);
        $this->assertEquals(1, $actions['shortcode_alias/alias_tag/content']);
        $this->assertEquals(1, $actions['shortcode_alias/alias_tag/output']);
    }

    //
    // Shortcode Assertions
    //

    /**
     * [assertAttsContains description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    protected function assertAttsContains($value)
    {
        $this->assertAttsContains($this->atts, $value,
            "Failed to assert that atts contains '$value'"
        );
    }

    /**
     * [assertAttEquals description]
     * @param  [type] $key      [description]
     * @param  [type] $expected [description]
     * @return [type]           [description]
     */
    protected function assertAttEquals($key, $expected)
    {
        $this->assertEquals($expected, $this->atts[$key],
            "Failed to assert that attribute $key equals '$expected'"
        );
    }

    /**
     * [assertContentEquals description]
     * @param  [type] $expected [description]
     * @return [type]           [description]
     */
    protected function assertContentEquals($expected)
    {
        $this->assertEquals($expected, $this->content,
            "Failed to assert that shortcode content equals '$expected'"
        );
    }
}
