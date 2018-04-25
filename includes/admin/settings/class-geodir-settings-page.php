<?php
/**
 * GeoDirectory Settings Page/Tab
 *
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Settings_Page', false ) ) :

/**
 * GeoDir_Settings_Page.
 */
abstract class GeoDir_Settings_Page {

	/**
	 * Setting page id.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Setting page label.
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'geodir_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'geodir_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );
		add_action( 'geodir_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'geodir_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'geodir_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	public function font_awesome_select(){
		?>
		<div  id="gd-font-awesome-select" class="gd-notification lity-hide noti-white">
		<select

			name="tab_icon"
			class="regular-text geodir-select"
			data-fa-icons="1"  tabindex="-1" aria-hidden="true"
		    onchange="jQuery('.gd-tabs-panel li form #field_icon').val(jQuery(this).val());jQuery('.lity-close').trigger('click');"
		>
			<?php
			include_once( dirname( __FILE__ ) . '/../settings/data_fontawesome.php' );
			echo "<option value=''>".__('None','geodirectory')."</option>";
			//$tab_icon = str_replace("fa ","",$tab->tab_icon);
			foreach ( geodir_font_awesome_array() as $key => $val ) {
				?>
				<option value="<?php echo esc_attr( $key ); ?>" data-fa-icon="<?php echo esc_attr( $key ); ?>" <?php
				//selected( $tab_icon, $key );
				?>><?php echo $key ?></option>
				<?php
			}
			?>
		</select>
		</div>
		<?php

	}

	/**
	 * Get settings page ID.
	 * @since 3.0.0
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get settings page label.
	 * @since 3.0.0
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Add this page to settings.
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		return apply_filters( 'geodir_get_settings_' . $this->id, array() );
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		return apply_filters( 'geodir_get_sections_' . $this->id, array() );
	}

	/**
	 * Detect if the advanced settings button should be shown or not.
	 *
	 * @return bool
	 */
	public function show_advanced(){

		if(isset($_REQUEST['page']) && $_REQUEST['page']=='gd-cpt-settings'){return true;} // if on CPT settings then show.

		global $current_section;
		$show = false;
		$settings = $this->get_settings($current_section);

		if(!empty($settings)){
			foreach($settings as $setting){
				if(isset($setting['advanced']) && $setting['advanced']){
					$show = true;
					break;
				}
			}
		}

//		print_r($settings);exit;
//return true;
		return $show;
	}

	/**
	 * Output the toggle show/hide advanced settings.
	 */
	public function output_toggle_advanced(){
		global $hide_advanced_toggle;

		if($hide_advanced_toggle){ return;}

		// check if we need to show advanced or not
		if(!$this->show_advanced()){return;}


		$this->toggle_advanced_button();

	}

	public static function toggle_advanced_button(){

		$show = geodir_get_option( 'admin_disable_advanced', false );

		if($show){return;} // don't show advanced toggle
		
		$text_show = __("Show Advanced","geodirectory");
		$text_hide = __("Hide Advanced","geodirectory");

		if(!$show){
			$css = "none";
			$text = $text_show;
			$toggle_CSS = '';
		}else{
			$css = "block";
			$text = $text_hide;
			$toggle_CSS = 'gda-hide';
		}
		?>
		<style>
			.gd-advanced-setting,#default_location_set_address_button{display: none;}
			.gd-advanced-setting.gda-show,#default_location_set_address_button.gda-show{display: block;}
			tr.gd-advanced-setting.gda-show{display: table-row;}
			li.gd-advanced-setting.gda-show{display: list-item;}
			/* Show Advanced */
			.gd-advanced-toggle .gdat-text-show {display: block;}
			.gd-advanced-toggle .gdat-text-hide {display: none;}

			/* Hide Advanced */
			.gd-advanced-toggle.gda-hide .gdat-text-show {display: none;}
			.gd-advanced-toggle.gda-hide .gdat-text-hide {display: block;}
		</style>

		<?php

		echo "<button class='button-primary gd-advanced-toggle $toggle_CSS' type=\"button\"  >";
		echo "<span class='gdat-text-show'>$text_show</span>";
		echo "<span class='gdat-text-hide'>$text_hide</span>";
		echo "</button>";

		?>
		<script>
			init_advanced_settings();
		</script>
		<?php
	}

	/**
	 * Output sections.
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=gd-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings();

		GeoDir_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings();
		GeoDir_Admin_Settings::save_fields( $settings );

		if ( $current_section ) {
			do_action( 'geodir_update_options_' . $this->id . '_' . $current_section );
		}
	}
}

endif;
