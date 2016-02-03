<?php

/**
 * This is the first upgrade with the new system.
 *
 * The only thing this does is change the database version to 2, since we will use integers now.
 *
 * @since 1.9.27
 */
class Upgrade_183_2 implements NGG_Upgrade {

	/**
	 * {@inheritDoc}
     */
	public function apply() {
		return '2';
	}

	/**
	 * {@inheritDoc}
     */
	function undo() {
		return '1.8.3';
	}
}