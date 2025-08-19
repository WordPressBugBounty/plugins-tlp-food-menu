<?php
/**
 * Abstract Class for ElementorWidget.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Abstracts;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use Elementor\Widget_Base as Elementor;
use RT\FoodMenu\Helpers\Fns;

/**
 * Abstract Class for Controller.
 */
abstract class ElementorWidget extends Elementor {

	/**
	 * Widget Title.
	 *
	 * @var String
	 */
	protected $elName;

	/**
	 * Widget name.
	 *
	 * @var String
	 */
	protected $elBase;

	/**
	 * Widget categories.
	 *
	 * @var String
	 */
	protected $elCategory;

	/**
	 * Widget icon class.
	 *
	 * @var String
	 */
	protected $elIcon;

	/**
	 * Widget prefix.
	 *
	 * @var String
	 */
	public $elPrefix;

	/**
	 * Widget controls.
	 *
	 * @var array
	 */
	public $elControls = [];

	/**
	 * Class constructor.
	 *
	 * @param array $data default data.
	 * @param array $args default arg.
	 */

	public function __construct( $data = [], $args = null ) {
		$this->elCategory = 'rtfm-food-menu';
		parent::__construct( $data, $args );
	}

	/**
	 * Get widget name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->elBase;
	}

	/**
	 * Get widget title
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->elName;
	}

	/**
	 * Get widget icon
	 *
	 * @return string
	 */
	public function get_icon() {
		return $this->elIcon;
	}

	/**
	 * Widget Category
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ $this->elCategory ];
	}

	/**
	 * Widget Controls.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->layoutTab()->settingsTab()->styleTab();
		Fns::addElControls( $this->elControls, $this );

	}


	/**
	 * Starts an Elementor Section
	 *
	 * @param string $id Section ID.
	 * @param string $label Section label.
	 * @param object $tab Tab ID.
	 * @param array $conditions Section Condition.
	 * @param array $condition Section Conditions.
	 *
	 * @return void
	 */
	public function startSection( $id, $label, $tab, $conditions = [], $condition = [] ) {
		$this->elControls[] = [
			'mode'       => 'section_start',
			'id'         => $this->elPrefix . $id,
			'label'      => $label,
			'tab'        => $tab,
			'condition'  => $condition,
			'conditions' => $conditions,
		];
	}

	/**
	 * Ends an Elementor Section
	 *
	 * @return void
	 */
	public function endSection() {
		$this->elControls[] = [
			'mode' => 'section_end',
		];
	}

	/**
	 * Starts an Elementor tab group.
	 *
	 * @param string $id Tab ID.
	 * @param array $conditions Tab condition.
	 *
	 * @return void
	 */
	public function startTabGroup( $id, $conditions = [], $condition = [] ) {
		$this->elControls[] = [
			'mode'       => 'tabs_start',
			'id'         => $this->elPrefix . $id,
			'conditions' => $conditions,
			'condition'  => $condition,
		];
	}

	/**
	 * Ends an Elementor tab group.
	 *
	 * @param array $conditions Tab condition.
	 *
	 * @return void
	 */
	public function endTabGroup( $conditions = [], $condition = [] ) {
		$this->elControls[] = [
			'mode'       => 'tabs_end',
			'conditions' => $conditions,
			'condition'  => $condition,
		];
	}

	/**
	 * Starts an Elementor tab
	 *
	 * @param string $id Section ID.
	 * @param string $label Section label.
	 * @param array $conditions Tab condition.
	 *
	 * @return void
	 */
	public function startTab( $id, $label, $conditions = [], $condition = [] ) {
		$this->elControls[] = [
			'mode'       => 'tab_start',
			'id'         => $this->elPrefix . $id,
			'label'      => $label,
			'conditions' => $conditions,
			'condition'  => $condition,
		];
	}

	/**
	 * Ends an Elementor tab.
	 *
	 * @param array $conditions Tab condition.
	 *
	 * @return void
	 */
	public function endTab( $conditions = [], $condition = [] ) {
		$this->elControls[] = [
			'mode'       => 'tab_end',
			'conditions' => $conditions,
			'condition'  => $condition,
		];
	}

	/**
	 * Starts an Elementor tab
	 *
	 * @param string $id Heading ID.
	 * @param string $label Heading label.
	 * @param string $separator Section separator.
	 * @param array $conditions Section Condition.
	 * @param array $condition Section Conditions.
	 *
	 * @return void
	 */
	public function elHeading( $id, $label, $separator = null, $conditions = [], $condition = [] ) {
		$this->elControls[] = [
			'type'            => 'html',
			'id'              => $id,
			'raw'             => sprintf(
				'<h3 class="rtfm-elementor-group-heading">%s</h3>',
				$label
			),
			'separator'       => $separator,
			'content_classes' => 'elementor-panel-heading-title',
			'conditions'      => $conditions,
			'condition'       => $condition,
		];
	}

	/**
	 * Checks for preview mode.
	 *
	 * @return boolean
	 */
	public function isPreview() {
		return \Elementor\Plugin::$instance->preview->is_preview_mode() || \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	/**
	 * Controls for layout tab
	 *
	 * @return object
	 */
	abstract protected function layoutTab();

	/**
	 * Controls for settings tab
	 *
	 * @return object
	 */
	abstract protected function settingsTab();

	/**
	 * Controls for style tab
	 *
	 * @return object
	 */
	abstract protected function styleTab();

	public function get_widget_scripts( $prefix = '' ) {

		if ( TLPFoodMenu()->has_pro() ) {

			wp_enqueue_script( 'fmp-image-load' );
			wp_enqueue_script( 'fmp-isotope' );
			wp_enqueue_script( 'fmp-actual-height' );
			wp_enqueue_script( 'fmp-addon' );
			wp_enqueue_script( 'fmp-frontend' );

//			if ( 'grid' === $prefix ) {
//				wp_enqueue_script( 'grid' );
//			}
//			if ( 'list' === $prefix ) {
//				wp_enqueue_script( 'list' );
//			}

		}
	}
}

