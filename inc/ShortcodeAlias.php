<?php

class ShortcodeAlias
{

    /**
     * Alias shortcode tag [some_alias]
     * @var (string)
     */
    protected $tag;

    /**
     * Alias target shortcode tag
     * The "real" shortcode we are forwarding our data to
     * @var (string)
     */
    protected $alias_of;

    /**
     * Alias attribute defaults
     * Can also be thought of as an attribute "preset"
     * @var (array)
     */
    protected $defaults;

    /**
     * Original alias callback attributes & content
     * @var (array)
     */
    protected $origin;

    /**
     * Alias attributes to pass to the target
     * @var (array)
     */
    protected $atts;

    /**
     * Shortcode content
     * @var string
     */
    protected $content;

    /**
     * Target shortcode callback
     * @var (string|array)
     */
    protected $callback;



    public function __construct( $tag, $alias_of, $defaults = array() )
    {
        global $shortcode_tags;

        $this->tag      = $tag;
        $this->alias_of = $alias_of;
        $this->defaults = (array) $defaults;
        $this->callback = $shortcode_tags[ $alias_of ];
    }

    /**
     * Initialize the alias
     */
    public function init()
    {
        add_shortcode( $this->tag, array($this, 'shortcode_handler') );

        add_filter( "shortcode_alias/$this->tag/atts", array($this, 'default_atts'), 10, 2 );
        add_filter( "shortcode_alias/$this->tag/content", array($this, 'default_content'), 10, 2 );
    }

    /**
     * Alias Shortcode Callback
     *
     * Here we will forward the call to it's destination callback
     * and allow for some cool stuff in the process.
     *
     * @param  (string|array)    $atts    alias atts
     * @param  (string)            $content alias enclosed content
     * @return target
     */
    public function shortcode_handler( $atts, $content )
    {
        $this->origin = compact('atts','content');

        /**
         * @filter 'shortcode_alias/{tag}/atts'
         * @param $atts
         */
        $this->atts = (array) apply_filters( "shortcode_alias/$this->tag/atts", (array) $atts, $this );

        /**
         * @filter 'shortcode_alias/{tag}/content'
         * @param $content
         */
        $this->content = apply_filters( "shortcode_alias/$this->tag/content", $content, $this );


        return $this->call();
    }

    /**
     * This is where the magic happens
     * @return target shortcode returned
     */
    protected function call()
    {
        $output = call_user_func( $this->callback, $this->atts, $this->content, $this->alias_of );

        /**
         * filter	'shortcode_alias/{tag}/output'
         * @since	0.1
         * @param	(mixed)	$output	shortcode callback returned output - probably string
         * @param   ShortcodeAlias
         */
        return apply_filters( "shortcode_alias/{$this->tag}/output", $output, $this );
    }

    /**
     * Filter callback for applying the registered default attributes
     *
     * @param $atts
     * @param $instance
     *
     * @return mixed
     */
    public function default_atts( $atts, $instance )
    {
        if ( $instance !== $this || empty( $this->defaults['atts'] ) ) return $atts;

        foreach ( (array) $this->defaults['atts'] as $key => $default_value )
        {
            $current_value = isset( $atts[ $key ] ) ? $atts[ $key ] : null;
            $atts[ $key ] = $this->apply_default( $current_value, $default_value );
        }

        return $atts;
    }

    /**
     * Filter callback for applying the registered default content
     *
     * @param $content
     * @param $instance
     *
     * @return mixed
     */
    public function default_content( $content, $instance )
    {
        if ( $instance !== $this ) return $content;

        if ( $content === '' ) $content = null;

        return $this->apply_default( $content, $this->defaults['content'] );
    }

    /**
     * @param $current_value
     * @param $default_value
     *
     * @return mixed
     */
    protected function apply_default( $current_value, $default_value )
    {
        // simple default

        if ( ! is_array( $default_value ) )
        {
            return is_null( $current_value )
                ? $default_value
                : $current_value;
        }

        // complex default

        $default_value = wp_parse_args( $default_value, array(
            'default'  => '',
            'prepend'  => '',
            'append'   => '',
        ) );

        // override shortcode-passed value completely
        if ( isset( $default_value['override'] ) ) {
            return $default_value['override'];
        }

        if ( is_null( $current_value ) ) {
            $current_value = $default_value['default'];
        }

        if ( $default_value['prepend'] ) {
            $current_value = $default_value['prepend'] . $current_value;
        }

        if ( $default_value['append'] ) {
            $current_value .= $default_value['append'];
        }

        return $current_value;
    }

    function __get( $prop )
    {
        return isset( $this->$prop )
            ? $this->$prop
            : null;
    }
}
