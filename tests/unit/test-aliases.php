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
        add_shortcode_alias('test_alias_defaults', 'test', [
            'atts' => [
                'a' => 'b',
                'apple' => 'orange'
            ]
        ]);

        do_shortcode('[test_alias_defaults]');

        $this->assertAttEquals('a', 'b');
        $this->assertAttEquals('apple', 'orange');

        // defaults are overriden by passed attributes

        do_shortcode('[test_alias_defaults a=z apple=juice]');

        $this->assertAttEquals('a', 'z');
        $this->assertAttEquals('apple', 'juice');
    }


     // Shortcode Assertions

     /**
      * [assertAttsContains description]
      * @param  [type] $value [description]
      * @return [type]        [description]
      */
    protected function assertAttsContains($value)
    {
        $this->assertAttsContains($this->atts, $value,
            "Failed to assert that atts contains $value"
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
            "Failed to assert that attribute '$key' = $expected"
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
