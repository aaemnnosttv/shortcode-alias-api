<?php

/**
 * Initializes the factory and stores the global instance
 * @return ShortcodeAliasFactory
 */
function ShortcodeAliasFactory()
{
    static $instance;
    if ( ! $instance ) {
        $instance = new ShortcodeAliasFactory();
    }

    return $instance;
}


/**
 * Register a NEW shortcode as an alias of another shortcode
 *
 * Optionally define default values for attributes &/or prepend/append them
 * to the attributes passed by the shortcode!
 *
 * Note: function arguments differ from add_shortcode! (with the exception of the first)
 *
 * @param  string $tag name of shortcode to add
 * @param  string $alias_of tag of shortcode to "connect" to
 * @param  mixed $defaults array of default attributes => values
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
 *
 * @return ShortcodeAlias
 */
function add_shortcode_alias( $tag, $alias_of, $defaults = false )
{
    return ShortcodeAliasFactory()->alias( $tag, $alias_of, $defaults );
}

/**
 * Remove the alias and restore the previous callback if replaced with an alias
 * @param $tag
 * @return bool
 */
function remove_shortcode_alias( $tag )
{
    return ShortcodeAliasFactory()->revert( $tag );
}