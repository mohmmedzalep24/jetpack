<?php
/**
 * Defines Jetpack CRM Automation engine.
 *
 * @package automattic/jetpack-crm
 * @since $$next-version$$
 */

namespace Automattic\Jetpack\CRM\Automation;

/**
 * Automation Engine.
 *
 * @since $$next-version$$
 */
class Automation_Engine {

	/**
	 * Instance singleton.
	 *
	 * @since $$next-version$$
	 * @var Automation_Engine
	 */
	private static $instance = null;

	/**
	 * The triggers map name => classname.
	 *
	 * @since $$next-version$$
	 * @var string[]
	 */
	private $triggers_map = array();

	/**
	 * The steps map name => classname.
	 *
	 * @since $$next-version$$
	 * @var string[]
	 */
	private $steps_map = array();

	/**
	 * The Automation logger.
	 *
	 * @since $$next-version$$
	 * @var ?Automation_Logger
	 */
	private $automation_logger = null;

	/**
	 * The list of registered workflows.
	 *
	 * @since $$next-version$$
	 * @var Automation_Workflow[]
	 */
	private $workflows = array();

	/**
	 *  Instance singleton object.
	 *
	 * @since $$next-version$$
	 *
	 * @param bool $force Whether to force a new Automation_Engine instance.
	 * @return Automation_Engine The Automation_Engine instance.
	 */
	public static function instance( bool $force = false ): Automation_Engine {
		if ( ! self::$instance || $force ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Set the automation logger.
	 *
	 * @since $$next-version$$
	 *
	 * @param Automation_Logger $logger The automation logger.
	 */
	public function set_automation_logger( Automation_Logger $logger ) {
		$this->automation_logger = $logger;
	}

	/**
	 * Register a trigger.
	 *
	 * @since $$next-version$$
	 *
	 * @param string $trigger_classname Trigger classname to add to the mapping.
	 *
	 * @throws Automation_Exception Throws an exception if the trigger class does not match the expected conditions.
	 */
	public function register_trigger( string $trigger_classname ) {

		if ( ! class_exists( $trigger_classname ) ) {
			throw new Automation_Exception(
				/* Translators: %s is the name of the trigger class that does not exist. */
				sprintf( __( 'Trigger class %s does not exist', 'zero-bs-crm' ), $trigger_classname ),
				Automation_Exception::TRIGGER_CLASS_NOT_FOUND
			);
		}

		// Check if the trigger implements the interface
		if ( ! in_array( Trigger::class, class_implements( $trigger_classname ), true ) ) {
			throw new Automation_Exception(
				/* Translators: %s is the name of the trigger class that does not implement the Trigger interface. */
				sprintf( __( 'Trigger class %s does not implement the Trigger interface', 'zero-bs-crm' ), $trigger_classname ),
				Automation_Exception::TRIGGER_CLASS_NOT_FOUND
			);
		}

		// Check if the trigger has proper slug
		$trigger_slug = $trigger_classname::get_slug();

		if ( empty( $trigger_slug ) ) {
			throw new Automation_Exception(
				__( 'The trigger must have a non-empty slug', 'zero-bs-crm' ),
				Automation_Exception::TRIGGER_SLUG_EMPTY
			);
		}

		if ( array_key_exists( $trigger_slug, $this->triggers_map ) ) {
			throw new Automation_Exception(
				/* Translators: %s is the name of the trigger slug that already exists. */
				sprintf( __( 'Trigger slug already exists: %s', 'zero-bs-crm' ), $trigger_slug ),
				Automation_Exception::TRIGGER_SLUG_EXISTS
			);
		}

		$this->triggers_map[ $trigger_slug ] = $trigger_classname;
	}

	/**
	 * Register a step in the automation engine.
	 *
	 * @since $$next-version$$
	 *
	 * @param string $class_name The name of the class in which the step should belong.
	 *
	 * @throws Automation_Exception Throws an exception if the step class does not exist.
	 */
	public function register_step( string $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			throw new Automation_Exception(
				/* Translators: %s is the name of the step class that does not exist. */
				sprintf( __( 'Step class %s does not exist', 'zero-bs-crm' ), $class_name ),
				Step_Exception::DO_NOT_EXIST
			);
		}

		if ( ! in_array( Step::class, class_implements( $class_name ), true ) ) {
			throw new Automation_Exception(
				sprintf( 'Step class %s does not implement the Base_Step interface', $class_name ),
				Step_Exception::DO_NOT_EXTEND_BASE
			);
		}

		$step_name                     = $class_name::get_slug();
		$this->steps_map[ $step_name ] = $class_name;
	}

	/**
	 * Get a step class by name.
	 *
	 * @since $$next-version$$
	 *
	 * @param string $step_name The name of the step whose class we will be retrieving.
	 * @return string The name of the step class.
	 *
	 * @throws Automation_Exception Throws an exception if the step class does not exist.
	 */
	public function get_step_class( string $step_name ): string {
		if ( ! isset( $this->steps_map[ $step_name ] ) ) {
			throw new Automation_Exception(
				/* Translators: %s is the name of the step class that does not exist. */
				sprintf( __( 'Step %s does not exist', 'zero-bs-crm' ), $step_name ),
				Automation_Exception::STEP_CLASS_NOT_FOUND
			);
		}
		return $this->steps_map[ $step_name ];
	}

	/**
	 * Add a workflow.
	 *
	 * @since $$next-version$$
	 *
	 * @param Automation_Workflow $workflow The workflow class instance to be added.
	 * @param bool                $init_workflow Whether or not to initialize the workflow.
	 *
	 * @throws Workflow_Exception Throws an exception if the workflow is not valid.
	 */
	public function add_workflow( Automation_Workflow $workflow, bool $init_workflow = false ) {
		$this->workflows[] = $workflow;

		if ( $init_workflow ) {
			$workflow->init_triggers();
		}
	}

	/**
	 * Build and add a workflow.
	 *
	 * @since $$next-version$$
	 *
	 * @param array $workflow_data The workflow data to be added.
	 * @param bool  $init_workflow Whether or not to initialize the workflow.
	 * @return Automation_Workflow The workflow class instance to be added.
	 *
	 * @throws Workflow_Exception Throws an exception if the workflow is not valid.
	 */
	public function build_add_workflow( array $workflow_data, bool $init_workflow = false ): Automation_Workflow {
		$workflow = new Automation_Workflow( $workflow_data );
		$this->add_workflow( $workflow, $init_workflow );

		return $workflow;
	}

	/**
	 * Init automation workflows.
	 *
	 * @since $$next-version$$
	 *
	 * @throws Workflow_Exception Throws an exception if the workflow is not valid.
	 */
	public function init_workflows() {

		/** @var Automation_Workflow $workflow */
		foreach ( $this->workflows as $workflow ) {
			$workflow->init_triggers();
		}
	}

	/**
	 * Get step instance.
	 *
	 * @since $$next-version$$
	 *
	 * @param string $step_name The name of the step to be registered.
	 * @param array  $step_data The step data to be registered.
	 * @return Step The step class instance.
	 *
	 * @throws Automation_Exception Throws an exception if the step class does not exist.
	 */
	public function get_registered_step( $step_name, array $step_data = array() ): Step {

		$step_class = $this->get_step_class( $step_name );

		if ( ! class_exists( $step_class ) ) {
			throw new Automation_Exception(
				/* Translators: %s is the name of the step class that does not exist. */
				sprintf( __( 'Step class %s does not exist', 'zero-bs-crm' ), $step_class ),
				Automation_Exception::STEP_CLASS_NOT_FOUND
			);
		}

		return new $step_class( $step_data );
	}

	/**
	 * Get registered steps.
	 *
	 * @since $$next-version$$
	 *
	 * @return string[] The registered steps.
	 */
	public function get_registered_steps(): array {
		return $this->steps_map;
	}

	/**
	 * Get trigger instance.
	 *
	 * @since $$next-version$$
	 *
	 * @param string $trigger_slug The name of the trigger slug with which to retrieve the trigger class.
	 * @return string The name of the trigger class.
	 *
	 * @throws Automation_Exception Throws an exception if the step class does not exist.
	 */
	public function get_trigger_class( string $trigger_slug ): string {

		if ( ! isset( $this->triggers_map[ $trigger_slug ] ) ) {
			throw new Automation_Exception(
				/* Translators: %s is the name of the step class that does not exist. */
				sprintf( __( 'Trigger %s does not exist', 'zero-bs-crm' ), $trigger_slug ),
				Automation_Exception::TRIGGER_CLASS_NOT_FOUND
			);
		}

		return $this->triggers_map[ $trigger_slug ];
	}

	/**
	 * Get Automation logger.
	 *
	 * @since $$next-version$$
	 *
	 * @return Automation_Logger Return an instance of the Automation_Logger class.
	 */
	public function get_logger(): Automation_Logger {
		return $this->automation_logger ?? Automation_Logger::instance();
	}

	/**
	 * Get the registered triggers.
	 *
	 * @since $$next-version$$
	 *
	 * @return string[] The registered triggers.
	 */
	public function get_registered_triggers(): array {
		return $this->triggers_map;
	}

}
