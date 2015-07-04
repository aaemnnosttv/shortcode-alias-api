<?php


class ShortcodeAliasFactory
{
    protected $aliases = array();

    /**
     * @param $tag
     * @return bool
     */
    public function alias_exists( $tag )
    {
        return (bool) end( $this->aliases[ $tag ] );
    }

    /**
     * @param $tag
     * @return ShortcodeAlias|bool
     */
    public function get_alias( $tag )
    {
        return end( $this->aliases[ $tag ] );
    }

    /**
     * Register a new shortcode alias
     *
     * @param $tag
     * @param $alias_of
     * @param array|bool|false $defaults
     * @return ShortcodeAlias
     */
    public function alias( $tag, $alias_of, $defaults = array() )
    {
        $defaults = wp_parse_args( $defaults, array(
            'atts' => array(),
            'content' => ''
        ) );
        $alias = new ShortcodeAlias( $tag, $alias_of, $defaults );
        $alias->init();
        $this->aliases[ $tag ][ ] = $alias;
        return $alias;
    }

    /**
     * Reset the aliased shortcode's callback to the next alias in the stack,
     * or if none, remove the aliased shortcode and restore the target callback
     * @param $tag
     * @return bool
     */
    public function revert( $tag )
    {
        if ( ! $alias = array_pop( $this->aliases[ $tag ] ) ) return false;

        // see if we have another alias in the stack for the same tag
        if ( $this->alias_exists( $tag ) )
        {
            $alias = $this->get_alias();
            $alias->init();
            return true;
        }

        // reset the target shortcode's callback
        add_shortcode( $alias->alias_of, $alias->callback );
        remove_shortcode( $tag );

        return true;
    }
}


