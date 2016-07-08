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
            'atts'    => array(),
            'content' => ''
        ) );

        $alias = new ShortcodeAlias( $tag, $alias_of, $defaults );
        $alias->init();


        $this->aliases[ $tag ][] = $alias;

        return $alias;
    }

    /**
     * Reset the aliased shortcode's callback to the next alias in the stack,
     * or if none, remove the aliased shortcode and restore the target callback
     *
     * @param      $tag
     * @param bool $all  whether all aliases for the tag should be reverted or only the currently active alias
     *
     * @return bool
     */
    public function revert( $tag, $all = false )
    {
        if ( ! $this->alias_exists( $tag ) ) return false;

        if ( $all ) {
            return $this->revert_all_for_tag( $tag );
        }

        return $this->revert_tag( $tag );
    }

	/**
     * Reset and remove all aliases
     */
    public function revert_all()
    {
        foreach ( $this->aliases as $tag => $alias )
        {
            $this->revert_all_for_tag( $tag );
        }
    }

    /**
     * Reverts the entire alias stack for a given tag
     *
     * @param $tag
     */
    protected function revert_all_for_tag( $tag )
    {
        while ( $this->revert_tag( $tag ) ) {
            // ...
        }
    }

    /**
     * @param $tag
     *
     * @return bool  whether or not the alias was reverted
     */
    protected function revert_tag( $tag )
    {
        if ( ! $alias = array_pop( $this->aliases[ $tag ] ) ) {
            return false;
        }
        /* @var $alias ShortcodeAlias */

        // reset the target shortcode's callback
        add_shortcode( $alias->alias_of, $alias->callback );

        // see if we have another alias in the stack for the same tag
        // if we do, initialize it and stop there
        if ( $this->alias_exists( $tag ) )
        {
            $alias = $this->get_alias( $tag );
            $alias->init();

            return true;
        }

        // at this point all aliases have been removed internally
        // remove the alias shortcode if it does not share the target tag
        if ( $tag != $alias->alias_of ) {
            remove_shortcode( $tag );
        }

        return true;
    }
}


