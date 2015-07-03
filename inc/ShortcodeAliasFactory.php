<?php


class ShortcodeAliasFactory
{
    private $aliases = array();

    /**
     * @param $tag
     * @return bool
     */
    public function alias_exists( $tag )
    {
        return isset( $this->aliases[ $tag ] );
    }

    /**
     * @param $tag
     * @return bool
     */
    public function get_alias( $tag )
    {
        if ( $this->alias_exists( $tag ) )
            return $this->aliases[ $tag ];
        else
            return false;
    }

    /**
     * Register a new shortcode alias
     *
     * @param $tag
     * @param $alias_of
     * @param bool|false $defaults
     * @return ShortcodeAlias
     */
    public function alias( $tag, $alias_of, $defaults = false )
    {
        $alias = new ShortcodeAlias( $tag, $alias_of, $defaults );
        $this->aliases[ $tag ] = $alias;
        return $alias;
    }

    /**
     * Remove the aliased shortcode and restore to original if there was one
     * @param $tag
     * @return bool
     */
    public function revert( $tag )
    {
        global $shortcode_tags;

        if ( ! $alias = $this->get_alias( $tag ) ) return false;

        $alias_of = $alias->get('alias_of');
        $callback = $alias->get('callback');
        $shortcode_tags[ $alias_of ] = $callback;
        unset( $this->aliases[ $tag ] );

        return true;
    }
}


