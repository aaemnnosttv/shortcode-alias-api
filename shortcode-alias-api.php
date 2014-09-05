<?php
/*
	Plugin Name: Shortcode Alias API
	Description: A plugin for shortcode awesomeness
	Version: 0.1
	Author: Evan Mattson
	Author URI: http://aaemnnost.tv
	Plugin URI: https://github.com/aaemnnosttv/shortcode-alias-api
	License: GPL2
*/

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
	 * Alias callback data
	 * @var (array)
	 */
	protected $args;

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
	 * Target shortcode callback
	 * @var (string|array)
	 */
	protected $callback;



	function __construct( $tag, $alias_of, $defaults = false )
	{
		global $shortcode_tags;

		$this->tag      = $tag;
		$this->alias_of = $alias_of;
		$this->defaults = $defaults;
		$this->callback = $shortcode_tags[ $alias_of ];

		add_shortcode( $tag, array($this, 'alias_handler') );
	}

	/**
	 * Alias Shortcode Callback
	 *
	 * Here we will forward the call to it's destination callback
	 * and allow for some cool stuff in the process.
	 *
	 * @param  (string|array)	$atts    alias atts
	 * @param  (string)			$content alias enclosed content
	 */
	function alias_handler( $atts, $content )
	{
		$args = array(
			'atts'    => $atts,
			'content' => $content,
		);
		$this->origin = $args;

		$this->atts = $atts;

		/**
		 * filter	'shortcode_alias/{tag}/args'
		 * @since	0.1
		 * @param	(array)	alias shortcode atts & content
		 */
		$this->args = apply_filters( "shortcode_alias/{$this->tag}/args", $args );

		if ( is_array( $this->defaults ) && $this->defaults )
			$this->apply_defaults();

		return $this->call();
	}

	/**
	 * This is where the magic happens
	 * @return target shortcode returned
	 */
	protected function call()
	{
		$content = $this->atts['__content'];
		unset( $this->atts['__content'] );

		/**
		 * filter	'shortcode_alias/{tag}/wrap'
		 * @since	0.1
		 * @param	(array)	
		 */
		$wrap = apply_filters( "shortcode_alias/{$this->tag}/wrap", array(
			'before' => '',
			'after'  => ''
		) );
		extract( $wrap );
		// $before
		// $after

		$output = call_user_func( $this->callback, $this->atts, $content, $this->alias_of );
		/**
		 * filter	'shortcode_alias/{tag}/output'
		 * @since	0.1
		 * @param	(mixed)	$output	shortcode callback returned output - probably string	
		 */
		$output = apply_filters( "shortcode_alias/{$this->tag}/output", $output ); 

		return $before . $output . $after;
	}

	protected function apply_defaults()
	{
		extract( $this->args );
		// $atts
		// $content

		// will be '' if no atts were matched in shortcode tag
		if ( !is_array( $atts ) )
			$atts = array();

		/**
		 * Enclosed shortcode content handling
		 */
		$atts['__content'] = $content;
		// now enclosed content can be modified the same way
		// this will be passed as $content to the target callback


		/**
		 * Default/atts handling
		 * Iterate through default settings and merge with passed atts
		 */
		foreach ( $this->defaults as $dkey => $default )
		{
			$mod = $this->get_key_mod( $dkey );

			// default is flagged to override any passed value
			if ( 'force_default' == $mod )
			{
				$key = ltrim( $dkey, '!' );
				$atts[ $key ] = $default;
				continue;
			}

			$key = trim( $dkey, '+' );

			if ( isset( $atts[ $key ] ) && $mod )
			{
				$atts[ $key ] = ( 'prepend' == $mod )
					? "{$default}{$atts[$key]}"
					: "{$atts[$key]}{$default}";
			}
			elseif ( isset( $atts[ $key ] ) )
			{
				// the key is not set to prepend/append the default
				// and the shortcode passed its own value
				// use the passed value
				continue;
			}
			else
				$atts[ $key ] = $default;
		}

		$this->atts = $atts;
	}

	/**
	 * Prepend/Append att key test
	 * @param  (string)	$a		attribute array key
	 * @return (string|bool)    string placement if detected, bool false otherwise
	 */
	function get_key_mod( $a )
	{
		if ( 0 === strpos( $a, '!' ) )
			return 'force_default';

		$len = strlen( $a );

		// check for a difference on either side
		if ( strlen( trim( $a, '+' ) ) === $len )
			return false;

		if ( strlen( ltrim( $a, '+' ) ) !== $len )
			return 'prepend';
		else
			return 'append';
	}

	function get( $prop )
	{
		return isset( $this->$prop )
			? $this->$prop
			: null;
	}

} // ShortcodeAlias


class ShortcodeAliasFactory
{
	private static $instance;

	private $aliases;

	public static function instance()
	{
		if ( is_null( self::$instance ) )
			self::$instance = new self();

		return self::$instance;
	}

	private function __construct()
	{
		$this->aliases = array();
	}

	public function alias_exists( $tag )
	{
		return isset( $this->aliases[ $tag ] );
	}

	public function get_alias( $tag )
	{
		if ( $this->alias_exists( $tag ) )
			return $this->aliases[ $tag ];
		else
			return false;
	}

	public function alias( $tag, $alias_of, $defaults = false )
	{
		$alias = new ShortcodeAlias( $tag, $alias_of, $defaults );
		$this->aliases[ $tag ] = $alias;
		return $alias;
	}

	public function revert( $tag )
	{
		global $shortcode_tags;

		if ( $alias = $this->get_alias( $tag ) )
		{
			$alias_of = $alias->get('alias_of');
			$callback = $alias->get('callback');
			$shortcode_tags[ $alias_of ] = $callback;
			unset( $this->aliases[ $tag ] );

			return true;
		}

		return false;
	}
}

/**
 * Register a NEW shortcode as an alias of another shortcode
 *
 * Optionally define default values for attributes &/or prepend/append them
 * to the attributes passed by the shortcode!
 *
 * Note: function arguments differ from add_shortcode! (with the exception of the first)
 *
 * @param  string	$tag 		name of shortcode to add
 * @param  string	$alias_of	tag of shortcode to "connect" to
 * @param  mixed 	$defaults 	array of default attributes => values
 *
 * prepend / append
 * these are always applied as they are additive
 * The `+` denotes where the default value will be relative to the
 * shortcode-passed value.
 * E.g.:
 * `+content` (prepend)
 * `content+` (append) 
 * 
 * prepend a value:
 * +class => 'someclass ' with a shortcode that passes class="myclass"
 * will produce an html class attribute class="someclass myclass"
 *
 * append a value:
 * class+ => ' someclass' with a shortcode that passes class="myclass"
 * will produce an html class attribute class="myclass someclass"
 *
 * Defaults (no prepend/append):
 * defined values that are added if there is no existing value for the attribute
 * passed shortcode values will override defined defaults completely
 */
function add_shortcode_alias( $tag, $alias_of, $defaults = false )
{
	$alias = ShortcodeAliasFactory::instance()->alias( $tag, $alias_of, $defaults );
	return $alias;
}

// restore original callback
function remove_shortcode_alias( $tag )
{
	return ShortcodeAliasFactory::instance()->revert( $tag );
}