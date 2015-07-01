<?php

include_once( 'interface-ngg-upgrade.php' );

/**
 * Class NGG_Upgrader
 *
 * The class responsible for upgrading the NextCellent database.
 *
 * This class automatically loads all upgrade schemes in the folder upgrades. These should be named as such:
 *  'upgrade-[OLD]-[NEW].php'. If not, the file will be ignored.
 *
 * The file should contain a class called Upgrade_[OLD]_to_[NEW], that implements NGG_Upgrade.
 *
 * @see NGG_Upgrade
 *
 * The class will only execute upgrades that are from and older version to this version.
 * For example, if the current (code) version is 4, and the database version is 2, it will execute these upgrades:
 *  upgrade-2-3.php
 *  upgrade-3-4.php
 *
 * These upgrades will not be executed:
 *  upgrade-1-2.php
 *  upgrade-4-5.php
 *
 * Note: the old upgrades are available in the source code repository.
 *
 */
class NGG_Upgrader {

	/**
	 * @var string[] $upgrades The filenames of the upgrades that should be executed.
	 */
	private $upgrades = array();

	/**
	 * @var int $new_version The new version (code version).
	 */
	private $new_version;

	/**
	 * @var int $old_version The old version (from the database).
	 */
	private $old_version;

	/**
	 * Load all upgrade classes.
	 *
	 * @param int $new_version The current code version (current version).
	 */
	public function __construct( $new_version ) {

		$this->new_version = $new_version;

		//Get database version
		$this->old_version = get_option( 'ngg_db_version' );

		$files = scandir( dirname( __FILE__ ) . '/upgrades' );

		foreach ( $files as $file ) {
			$versions = array();
			$match    = preg_match( '/^upgrade-([0-9]+)-([0-9]+).php$/', $file, $versions );

			if ( ! ( $match === 0 || $match === false ) ) {

				$from = $versions[1];
				$to   = $versions[2];

				if (
					version_compare( $from, $this->old_version, '>=' ) &&
					version_compare( $from, $new_version, '<' ) &&
					version_compare( $to, $this->old_version, '>' ) &&
					version_compare( $to, $new_version, '<=' ) &&
					version_compare( $from, $to, '<' )
				) {
					array_push( $this->upgrades, $file );
				}
			}
		}
	}

	/**
	 * Do the upgrade. If an exception occurs during one of the upgrades, all successfully applied upgrades will stay
	 * applied.
	 *
	 * @throws Upgrade_Exception If something went wrong during the upgrade.
	 */
	public function upgrade() {

		$upgraded_to = $this->old_version;

		foreach ( $this->upgrades as $upgrade ) {

			//Include the file.
			include( dirname( __FILE__ ) . '/upgrades/' . $upgrade );

			//Convert the filename to the classname
			$class_name = rtrim( str_replace( '-', '_', ucfirst( $upgrade ) ), '.php' );

			try {
				$reflection = new ReflectionClass( $class_name );

				/**
				 * @var NGG_Upgrade $class
				 */
				$class = $reflection->newInstance();

				//If it is an upgrade, do it.
				if ( $class instanceof NGG_Upgrade ) {
					$upgraded_to = $class->apply();
				}
			} catch ( ReflectionException $e ) {
				//If the class could not be instantiated, do nothing.
			}
		}
		//Save the new version to the databse.
		update_option( 'ngg_db_version', $upgraded_to );
	}
}