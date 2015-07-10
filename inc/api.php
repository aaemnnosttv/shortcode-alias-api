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
 * Optionally define default values for attributes &/or prepend/append
 * to the attributes passed by the shortcode!
 *
 * @param  string $tag name of shortcode to add
 * @param  string $alias_of tag of shortcode to "connect" to
 * @param  mixed $defaults array of default values
 *                   eg:
 *                         array(
 *                             'atts' => array(
 *                                 'attribute' => 'default value',
 *                                     // or
 *                                 'attribute' => array(
 *                                     'default'  => 'default value',
 *                                     'prepend'  => 'Always prepend with this before the passed value ',
 *                                     'append'   => 'Always append with this after the passed value ',
 *                                     'override' => 'Always override the passed value with this'
 *                                 )
 *                             ),
 *                             'content' => 'default content for an enclosed shortcode'
 *                         )
 *
 *
 * @return ShortcodeAlias
 */
function add_shortcode_alias( $tag, $alias_of, $defaults = false )
{
    return ShortcodeAliasFactory()->alias( $tag, $alias_of, $defaults );
}

/**
 * Remove all aliases for the given tag and restore the original callback if replaced with an alias
 * @param $tag
 * @return bool
 */
function remove_shortcode_alias( $tag )
{
    return ShortcodeAliasFactory()->revert( $tag, true );
}

/**
 * Revert the last alias on the given tag
 *
 * @param $tag
 *
 * @return bool
 */
function revert_shortcode_alias( $tag )
{
    return ShortcodeAliasFactory()->revert( $tag );
}


/**
 * Revert and remove all shortcode aliases
 */
function remove_all_shortcode_aliases()
{
    ShortcodeAliasFactory()->revert_all();
}

/**
 * Test whether or not a given shortcode tag is an alias
 *
 * @param $tag
 *
 * @return bool
 */
function is_shortcode_alias( $tag )
{
    global $shortcode_tags;
    return ! empty( $shortcode_tags[ $tag ][ 0 ] )
           && $shortcode_tags[ $tag ][ 0 ] instanceof ShortcodeAlias;
}

