<?php
/**
 * Jetpack CRM Automation Add_Remove_Contact_Tag action.
 *
 * @package automattic/jetpack-crm
 * @since $$next-version$$
 */

namespace Automattic\Jetpack\CRM\Automation\Actions;

use Automattic\Jetpack\CRM\Automation\Base_Action;

/**
 * Adds the Add_Remove_Contact_Tag class.
 *
 * @since $$next-version$$
 */
class Add_Remove_Contact_Tag extends Base_Action {

	/**
	 * Get the slug name of the step.
	 *
	 * @since $$next-version$$
	 *
	 * @return string The slug name of the step.
	 */
	public static function get_slug(): string {
		return 'jpcrm/add_remove_contact_tag';
	}

	/**
	 * Get the title of the step.
	 *
	 * @since $$next-version$$
	 *
	 * @return string The title of the step.
	 */
	public static function get_title(): ?string {
		return 'Add / Remove Contact Tag Action';
	}

	/**
	 * Get the description of the step.
	 *
	 * @since $$next-version$$
	 *
	 * @return string|null The description of the step.
	 */
	public static function get_description(): ?string {
		return 'Action to add or remove the contact tag';
	}

	/**
	 * Get the type of the step.
	 *
	 * @since $$next-version$$
	 *
	 * @return string The type of the step.
	 */
	public static function get_type(): string {
		return 'contacts';
	}

	/**
	 * Get the category of the step.
	 *
	 * @since $$next-version$$
	 *
	 * @return string|null The category of the step.
	 */
	public static function get_category(): ?string {
		return 'actions';
	}

	/**
	 * Get the allowed triggers.
	 *
	 * @since $$next-version$$
	 *
	 * @return string[] The allowed triggers.
	 */
	public static function get_allowed_triggers(): ?array {
		return array();
	}

	/**
	 * Add / remove the tag to / from the contact via the DAL.
	 *
	 * @since $$next-version$$
	 *
	 * @param array $contact_data The contact data on which the tag is to be added / removed.
	 */
	public function execute( array $contact_data = array() ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		global $zbs;

		$zbs->DAL->contacts->addUpdateContactTags( $this->attributes ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

}
