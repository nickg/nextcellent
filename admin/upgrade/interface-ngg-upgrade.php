<?php

include_once( 'class-upgrade-exception.php' );

/**
 * Interface NGG_Upgrade
 *
 * The interface all upgrades must adhere to. If this is not followed, unexpected behavior might occur.
 *
 * @see NGG_Upgrader
 *
 * A class should be named 'upgrade-[OLD]-[NEW].php'. If not, the file will not work.
 *
 * The file should contain a class called Upgrade_[OLD]_to_[NEW], that implements this interface. The file should be
 * placed in the upgrades folder.
 *
 * This interface is included by the upgrader, so the actual class that implements it does not have to.
 */
interface NGG_Upgrade {

	/**
	 * Execute the upgrade. If the upgrade could not be completed successfully, an Upgrade_Exception should be
	 * thrown.
	 *
	 * @return int The version to which the database was upgraded.
	 *
	 * @throws Upgrade_Exception If something went wrong during the upgrade.
	 */
	function apply();

	/**
	 * Undo the upgrade. Every upgrade should be fully undoable. As of version 1.9.27 this is not in use, but it might
	 * be in the future.
	 *
	 * @return int|string The version to which it was downgraded. Note that this should always be an integer, but in the
	 *                    first upgrade it is a string to '1.8.3'.
	 *
	 * @throws Upgrade_Exception If something went wrong during the upgrade.
	 */
	function undo();

}