<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce
 * @since      1.0.0
 *
 * @package    Woo_Variations_As_Single_Product
 * @subpackage Woo_Variations_As_Single_Product/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Woo_Variations_As_Single_Product
 * @subpackage Woo_Variations_As_Single_Product/admin
 * @author     StorePlugin <contact@storeplugin.net>
 */
class Woo_Variations_As_Single_Product_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-variations-as-single-product-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-variations-as-single-product-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * warning if WooCommerce is not active
	 *
	 * @since    1.0.0
	 */
	public function missing_woocommerce_notice() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			?>
				<div class="notice notice-error">
					<p><?php _e( 'Variations as Single Product for WooCommerce requires WooCommerce to be installed and activated', 'wc-variations-as-single-product' ); ?></p>
				</div>
			<?php
		}
	}

	/**
	 * Show notice on settings page.
	 * 
	 * @since    3.0.0
	 */
	public function settings_page_notice() {
		//if ( isset( $_GET['page'] ) && $_GET['page'] === 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] === 'sp_variations_as_product' ) {
			if ( defined( 'WC_VARIATIONS_AS_SINGLE_PRODUCT_PRO_VERSION' ) && version_compare( WC_VARIATIONS_AS_SINGLE_PRODUCT_PRO_VERSION, '3.4.0', '<' ) ) {
			?>
				<div class="notice notice-error">
					<p><?php echo sprintf(
    					__( 'You are using an outdated version of <strong>"Variations as Single Product (Pro)"</strong>, which limit functionality. Please update to the latest version from your account at %s to access all features.', 'wc-variations-as-single-product' ), 
    					'<a href="https://storeplugin.net/account/" target="_blank">storeplugin.net</a>'
					); ?></p>
				</div>
			<?php
			}
		//}
	}

	public function manual_update_notice_for_pro_plugin( $file, $plugin_data ) {
		if ( strpos( $file, 'wc-variations-as-single-product-pro.php' ) !== false && defined( 'WC_VARIATIONS_AS_SINGLE_PRODUCT_PRO_VERSION' ) && version_compare( WC_VARIATIONS_AS_SINGLE_PRODUCT_PRO_VERSION, '3.4.0', '<' )) {
			echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange">
					<div class="update-message notice inline notice-warning notice-alt">
						<p>There is a new version of Variations as Single Product for WooCommerce (Pro). <a href="https://storeplugin.net/account/" target="_blank">View version 3.4.0 details</a> <em>Automatic update is unavailable for this plugin</em></p>
					</div>
				  </td></tr>';
		}
	}	

	/**
	 * Add settings link to the plugin page.
	 * 
	 * @param array $links
	 * @param string $file
	 * @return array $links
	 */
	public function settings_link($links, $file ){
		$settings_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=sp_variations_as_product' ) . '">' . __( 'Settings', 'wc-variations-as-single-product' ) . '</a>';

		array_unshift( $links, $settings_link );

		if( !defined('WC_VARIATIONS_AS_SINGLE_PRODUCT_PRO_VERSION') ){
			$links['get_pro'] = '<a class="wvasp-get-pro" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">' . __( 'Upgrade to Pro', 'wc-variations-as-single-product' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Add variation tab to the WooCommerce settings tabs array.
	 * 
	 * @param array $settings_tabs
	 * @return array $settings_tabs
	 */
	public function add_settings_tab( $tabs ) {
		$tabs['sp_variations_as_product'] = __( 'Variations as Product', 'wc-variations-as-single-product' );
		return $tabs;
	}

	/**
	 * Add settings fields to the variations as product tab.
	 * 
	 * @return void
	 */
	public function settings_tab() {
		woocommerce_admin_fields( $this->get_variations_as_product_settings() );
	}

	/**
	 * Show settings fields for the variations as product tab.
	 * 
	 * @return array $settings Array of settings fields
	 */
	public function get_variations_as_product_settings() {
		$enable_variations_as_product = get_option( 'wvasp_enable_variations_as_product', 'no' );
		$disable_category_page_single_variation = get_option( 'wvasp_disable_category_page_single_variation', 'no' );
		$disable_tag_page_single_variation = get_option( 'wvasp_disable_tag_page_single_variation', 'no' );
		$disable_search_page_single_variation = get_option( 'wvasp_disable_search_page_single_variation', 'no' );
		$hide_parent_products   = get_option( 'wvasp_hide_parent_products', 'no' ); //wvasp
		$exclude_category_fields   = get_option( 'wvasp_exclude_category_fields', array() );
		$exclude_child_category_fields   = get_option( 'wvasp_exclude_child_category_fields', 'no' );
		$exclude_tag_fields        = get_option( 'wvasp_exclude_tag_fields', array() );

		$settings = array(
			'section_title' => array(
				'name'     => __( 'Variations as Product Options', 'wc-variations-as-single-product' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'variations_as_product_section_title'
			),
			'enable_variations_as_product' => array(
				'name'     => __( 'Enable Variations as Product', 'wc-variations-as-single-product' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Check this box to enable variations as individual products in your store.', 'wc-variations-as-single-product' ),
				'id'       => 'wvasp_enable_variations_as_product',
				'checked' => $enable_variations_as_product === 'yes',
				'value'	=> $enable_variations_as_product,
			),
			'disable_category_page_single_variation' => array(
				'type'     => 'checkbox',
				'desc'     => __( 'Disable on Category Page to display variations as a single product', 'wc-variations-as-single-product' ),
				'id'       => 'wvasp_disable_category_page_single_variation',
				'checked' => $disable_category_page_single_variation === 'yes',
				'value'	=> $disable_category_page_single_variation,
			),
			'disable_tag_page_single_variation' => array(
				'type'     => 'checkbox',
				'desc'     => __( 'Disable on Tag Page to display variations as a single product', 'wc-variations-as-single-product' ),
				'id'       => 'wvasp_disable_tag_page_single_variation',
				'checked' => $disable_tag_page_single_variation === 'yes',
				'value'	=> $disable_tag_page_single_variation,
			),
			'disable_search_page_single_variation' => array(
				'type'     => 'checkbox',
				'desc'     => __( 'Disable on Search Page to display variations as a single product', 'wc-variations-as-single-product' ),
				'id'       => 'wvasp_disable_search_page_single_variation',
				'checked' => $disable_search_page_single_variation === 'yes',
				'value'	=> $disable_search_page_single_variation,
			),
			'hide_parent_products' => array(
				'name'    => __( 'Hide Parent Products', 'wc-variations-as-single-product' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Enable to hide parent products on shop/category pages when it has variations.', 'wc-variations-as-single-product' ),
				'id'      => 'wvasp_hide_parent_products',
				'default' => 'no',
				'checked' => $hide_parent_products === 'yes',
				'value'   => $hide_parent_products
			),
			'enable_filter_by_attribute' => array(
				'name'    => __( 'Enable Filter by Attribute', 'wc-variations-as-single-product' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Enable to filter single variant by attributes like color, size etc on shop/category pages.', 'wc-variations-as-single-product' ). ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>',
				'id'      => 'wvasp_enable_filter_by_attribute',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'default' => 'no',
				'checked' => 'no',
				'value'   => 'no',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'exclude_category_fields' => array(
				'name'     => __( 'Exclude Product Categories', 'woo-variations-as-single-product' ),
				'type'     => 'multiselect',
				'desc'     => __( 'Select the product categories that will be excluded from single variation.', 'woo-variations-as-single-product' ),
				'id'       => 'wvasp_exclude_category_fields',
				'class'    => 'wc-enhanced-select',
				'options'  => $this->get_woocommerce_category_list(),
				'value' => $exclude_category_fields,
			),
			'exclude_child_category_fields' => array(
				'type'     => 'checkbox',
				'desc'     => __( 'Don\'t exclude child categories of the selected excluded categories.', 'wc-variations-as-single-product' ),
				'id'       => 'wvasp_exclude_child_category_fields',
				'checked' => $exclude_child_category_fields === 'yes',
				'value'	=> $exclude_child_category_fields,
			),
			'exclude_tag_fields' => array(
				'name'     => __( 'Exclude Product Tags', 'woo-variations-as-single-product' ),
				'type'     => 'multiselect',
				'desc'     => __( 'Select the product tags that will be excluded from single variation.', 'woo-variations-as-single-product' ),
				'id'       => 'wvasp_exclude_tag_fields',
				'class'    => 'wc-enhanced-select',
				'options'  => $this->get_woocommerce_tag_list(),
				'value' => $exclude_tag_fields,
			),
			'exclude_attributes_fields' => array(
				'name'     => __( 'Exclude Product Attributes', 'woo-variations-as-single-product' ),
				'type'     => 'text',
				'desc'     => __( 'Select the product attribute that will be excluded from single variation.', 'woo-variations-as-single-product' ) . ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>',
				'id'       => 'wvasp_exclude_attributes_fields',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'class'    => 'wvasp-text-field',
				'value'    => '',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'ignore_combine_attribute_variants' => array(
				'name'    => __( 'Ignore Combine Attributes', 'wc-variations-as-single-product' ),
				'type'    => 'checkbox',
				'desc'    => __( 'When excluding a product attribute, retain the variant if it has multiple attributes.', 'wc-variations-as-single-product' ) . ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>',
				'desc_tip' => __( 'E.g.  Excluding the <em>`pa_color`</em> attribute will only exclude variations with <em>`pa_color`</em> alone, but will retain variants that have both <em>`pa_color`</em> and <em>`pa_size`</em> or other combinations.', 'wc-variations-as-single-product' ),
				'id'      => 'wvasp_ignore_combine_attribute_variants',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'default' => 'no',
				'value'    => '',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'disable_cart_for_excluded_attributes' => array(
				'name'    => __( 'Disable Cart Button for Excluded Attributes Product', 'wc-variations-as-single-product' ),
				'type'    => 'checkbox',
				'desc'    => __( 'When an attribute is excluded, the first available option is automatically selected to add to the cart. Instead, display a "Select Options" link to the product page.', 'wc-variations-as-single-product' ) . ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>',
				'desc_tip' => __( 'Excluding any attribute (e.g. <em>`pa_size`</em>) causes the add-to-cart process to automatically select a size (e.g., <em>`XL`</em>). To prevent this, replace the add-to-cart button with a <em>"Select Options"</em> button directs to the product page, similar to the parent variable product.', 'wc-variations-as-single-product' ),
				'id'      => 'wvasp_disable_cart_for_excluded_attributes',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'default' => 'no',
				'value'    => '',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'variation_title_field' => array(
				'name'     => __( 'Single Variation Title', 'woo-variations-as-single-product' ),
				'type'     => 'text',
				'desc'     => __( 'Global title for variation single product. Use <strong>{title}</strong> for parent title and <strong>{attributes}</strong> for attributes values.', 'woo-variations-as-single-product' ). ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>'.__( '<br/>E.g. {title} in {attributes} => Hoodie in Red, XXL<br/>E.g. {title} in {attributes} => Hoodie in Color Red <em>(Attributes Structure : {attribute_name} {attribute_value})</em><br/>E.g. {attributes} {title} => Red Hoodie <em>(Attributes Structure : {attribute_value})</em>', 'woo-variations-as-single-product' ),
				'id'       => 'wvasp_variation_title_field',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'class'    => 'wvasp-text-field',
				'value'    => '',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'variation_title_attributes_field' => array(
				'name'     => __( 'Attributes Structure', 'woo-variations-as-single-product' ),
				'type'     => 'text',
				'desc'     => __( 'Defines how attribute data is structured. Use <strong>{attribute_name}</strong> for attribute\'s name and <strong>{attribute_value}</strong> for value.'). ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>'.__('<br/>E.g. {attribute_name} {attribute_value} => Color Red<br/>E.g. {attribute_name}: {attribute_value} => Color: Red<br/>E.g. {attribute_value} => Red', 'woo-variations-as-single-product' ),
				'id'       => 'wvasp_variation_title_attributes_field',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'class'    => 'wvasp-text-field',
				'value'    => '',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'variation_title_attributes_seperator_field' => array(
				'name'     => __( 'Attributes Seperator', 'woo-variations-as-single-product' ),
				'type'     => 'text',
				'desc'     => __( 'Specifies the character used to separate attribute values, such as comma (,) or "and".', 'woo-variations-as-single-product' ). ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>',
				'id'       => 'wvasp_variation_title_attributes_seperator_field',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'class'    => 'wvasp-text-field',
				'value'    => '',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'sort_variation_product' => array(
				'name'    => __( 'Sort variations product', 'wc-variations-as-single-product' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Place single variation product to there actual position. Generally, single variation products are displayed at the end of the product list.', 'wc-variations-as-single-product' ). ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>',
				'id'      => 'wvasp_sort_variation_product',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'default' => 'no',
				'checked' => 'no',
				'value'   => 'no',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'sort_popularity_variation_product' => array(
				'name'    => __( 'Sort(Popularity) variations product', 'wc-variations-as-single-product' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Sort variation products based on individual popularity. The parent product’s popularity reflects the combined popularity of all its variations, while each variation displays popularity based on its specific ranking within orders.', 'wc-variations-as-single-product' ). ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>',
				'id'      => 'wvasp_sort_popularity_variation_product',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'default' => 'no',
				'checked' => 'no',
				'value'   => 'no',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'sort_rating_variation_product' => array(
				'name'    => __( 'Sort(Rating) variations product', 'wc-variations-as-single-product' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Sort variation products based on the parent product\'s rating. Each variation inherits the rating directly from its parent product, ensuring consistent display across all variations.', 'wc-variations-as-single-product' ). ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>',
				'id'      => 'wvasp_sort_rating_variation_product',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'default' => 'no',
				'checked' => 'no',
				'value'   => 'no',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'sort_date_variation_product' => array(
				'name'    => __( 'Sort(Date) variations product', 'wc-variations-as-single-product' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Match single variation product date to parent product date. Variation products have their own dates, so when sorting by date, they appear in different positions than the parent product.', 'wc-variations-as-single-product' ). ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>',
				'id'      => 'wvasp_sort_date_variation_product',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'default' => 'no',
				'checked' => 'no',
				'value'   => 'no',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'hide_out_of_stock_variation_product' => array(
				'name'    => __( 'Hide \'Out of Stock\' Variations', 'wc-variations-as-single-product' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Hide out-of-stock product variations from the shop list. If a variation is out of stock or managed by stock quantity and the quantity is 0 or less, it will be hidden.', 'wc-variations-as-single-product' ). ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>',
				'id'      => 'wvasp_hide_out_of_stock_variation_product',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'default' => 'no',
				'checked' => 'no',
				'value'   => 'no',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'hie_backorder_variation_product' => array(
				'name'    => __( 'Hide Backorder Variations', 'wc-variations-as-single-product' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Hide product variations with backorder status. Sometimes, it\'s necessary to hide backorder variations, and this setting will enable that. (Will work with both \'Allow, but notify customer\' and 
				\'Allow\')', 'wc-variations-as-single-product' ). ' <a class="premium-tag-link" href="https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce/?utm_source=activesite&utm_campaign=singlevar&utm_medium=link" target="_blank">'.__( 'Get Premium', 'wc-variations-as-single-product' ).'</a>',
				'id'      => 'wvasp_hie_backorder_variation_product',
				'row_class'	=> 'wvasp-pro-link-field-wrapper',
				'default' => 'no',
				'checked' => 'no',
				'value'   => 'no',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'section_end' => array(
				'type'     => 'sectionend',
				'id'       => 'variations_as_product_section_end'
			)
		);

		return apply_filters( 'woo_variations_as_single_product_settings', $settings );
	}
	
	/** 
	 * Save the new fields for the tab
	 * 
	 * @return void
	 */
	public function save_settings() {
		$wvasp_enable_variations_as_product = isset( $_POST['wvasp_enable_variations_as_product'] ) ? 'yes' : 'no';
		update_option( 'wvasp_enable_variations_as_product', $wvasp_enable_variations_as_product );

		$wvasp_disable_category_page_single_variation = isset( $_POST['wvasp_disable_category_page_single_variation'] ) ? 'yes' : 'no';
		update_option( 'wvasp_disable_category_page_single_variation', $wvasp_disable_category_page_single_variation );

		$wvasp_disable_tag_page_single_variation = isset( $_POST['wvasp_disable_tag_page_single_variation'] ) ? 'yes' : 'no';
		update_option( 'wvasp_disable_tag_page_single_variation', $wvasp_disable_tag_page_single_variation );

		$wvasp_disable_search_page_single_variation = isset( $_POST['wvasp_disable_search_page_single_variation'] ) ? 'yes' : 'no';
		update_option( 'wvasp_disable_search_page_single_variation', $wvasp_disable_search_page_single_variation );

		$wvasp_hide_parent_products = isset( $_POST['wvasp_hide_parent_products'] ) ? 'yes' : 'no';
		update_option( 'wvasp_hide_parent_products', $wvasp_hide_parent_products );
	
		$wvasp_exclude_category_fields = isset( $_POST['wvasp_exclude_category_fields'] ) ? array_map( 'sanitize_text_field', $_POST['wvasp_exclude_category_fields'] ) : array();
		update_option( 'wvasp_exclude_category_fields', $wvasp_exclude_category_fields );

		$wvasp_exclude_child_category_fields = isset( $_POST['wvasp_exclude_child_category_fields'] ) ? 'yes' : 'no';
		update_option( 'wvasp_exclude_child_category_fields', $wvasp_exclude_child_category_fields );
	
		$wvasp_exclude_tag_fields = isset( $_POST['wvasp_exclude_tag_fields'] ) ? array_map( 'sanitize_text_field', $_POST['wvasp_exclude_tag_fields'] ) : array();
		update_option( 'wvasp_exclude_tag_fields', $wvasp_exclude_tag_fields );
	}

	/** 
	 * Get WooCommerce category list options.
	 * 
	 * @return array $category_list
	 */
	public function get_woocommerce_category_list() {
		$categories = get_terms( array(
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
		) );
		$category_list = array();
		foreach ( $categories as $category ) {
			$category_list[$category->term_id] = $category->name;
		}
		return $category_list;
	}

	/** 
	 * Get WooCommerce tag list options.
	 * 
	 * @return array $tag_list
	 */
	public function get_woocommerce_tag_list() {
		$tags = get_terms( array(
			'taxonomy' => 'product_tag',
			'hide_empty' => false,
		) );
		$tag_list = array();
		foreach ( $tags as $tag ) {
			$tag_list[$tag->term_id] = $tag->name;
		}
		return $tag_list;
	}

	/** 
	 * Add a new tab to the variable product panel
	 * 
	 * @param array $tabs
	 * @return array $tabs
	 */
	public function add_single_variation_tab($tabs) {
		$tabs['single_variation'] = array(
			'label' => __('Single Variation', 'woo-variations-as-single-product'),
			'target' => 'single_variation_options',
			'priority' => 50,
			'class' => 'show_if_variable',
		);
		
		return $tabs;
	}

	/** 
	 * Add a new panel to the variable product panel
	 * 
	 * @return void
	 */
	public function display_single_variation_panel() {
		global $post;
    
		$product = wc_get_product($post->ID);
			
		?>
		<div id="single_variation_options" class="panel woocommerce_options_panel hidden show_if_variable">
			<div class="options_group">
				<?php

				// Add "Exclude Variations" checkbox
				woocommerce_wp_checkbox(
					array(
						'id'            => '_wvasp_single_exclude_varations',
						'label'         => __( 'Exclude Variations', 'woo-variations-as-single-product' ),
						'description'   => __( 'Check this box to exclude variations as single product.', 'woo-variations-as-single-product' ),
						'value' => $product->get_meta('_wvasp_single_exclude_varations', true),
					)
				);
    
				// Add "Hide Parent Products" checkbox
				woocommerce_wp_checkbox(
					array(
						'id'            => '_wvasp_single_hide_parent_product',
						'label'         => __( 'Hide Parent Product', 'woo-variations-as-single-product' ),
						'description'   => __( 'Check this box to hide the parent product when shows variations as single product.', 'woo-variations-as-single-product' ),
						'value' => $product->get_meta('_wvasp_single_hide_parent_product', true),
					)
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save the new panel data
	 * 
	 * @param mixed $post_id
	 * @return void
	 */
	public function save_single_variation_panel($post_id) {
		$product = wc_get_product($post_id);
		$product->update_meta_data('_wvasp_single_exclude_varations', isset($_POST['_wvasp_single_exclude_varations']) ? 'yes' : 'no');
		$product->update_meta_data('_wvasp_single_hide_parent_product', isset($_POST['_wvasp_single_hide_parent_product']) ? 'yes' : 'no');
		$product->save();
	}

	/**
	 * Add metabox for variable products
	 *
	 * @param mixed $loop
	 * @param mixed $variation_data
	 * @param mixed $variation
	 * @return void
	 */
	public function product_variation_meta_fields($loop, $variation_data, $variation) {
		$exclude_variation = isset( $variation_data['_wvasp_single_exclude_variation'][0] ) ? $variation_data['_wvasp_single_exclude_variation'][0] : '';
		$variation_title = isset( $variation_data['_wvasp_single_variation_title'][0] ) ? $variation_data['_wvasp_single_variation_title'][0] : '';
		
		// Add "Variation Title" text field
		woocommerce_wp_text_input(
			array(
				'id'            => 'variation_title[' . $variation->ID . ']',
				'label'         => __( 'Single Variation Title', 'woo-variations-as-single-product' ),
				'placeholder'   => '',
				'wrapper_class'	=> ' form-row',
				'description'   => __( 'Enter a custom title for this variation single product', 'woo-variations-as-single-product' ),
				'value'         => $variation_title,
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'            => 'exclude_variation[' . $variation->ID . ']',
				'label'         => __( '	Exclude this variation to show as single product.', 'woo-variations-as-single-product' ),
				'wrapper_class'	=> ' form-row exclude-variation-row',
				'value'         => $exclude_variation,
			)
		);
	}

	/**
	 * Save variation quantity fields
	 *
	 * @param int $variation_id
	 * @return void
	 */

	public function save_variation_settings_fields( $variation_id ) {
		// Save "Exclude Variations" checkbox
		$exclude_variation = isset( $_POST['exclude_variation'][ $variation_id ] ) ? 'yes' : 'no';
		update_post_meta( $variation_id, '_wvasp_single_exclude_variation', $exclude_variation );
		
		// Save "Variation Title" text field
		$variation_title = isset( $_POST['variation_title'][ $variation_id ] ) ? $_POST['variation_title'][ $variation_id ] : '';
		update_post_meta( $variation_id, '_wvasp_single_variation_title', $variation_title );
	}

	/**
	 * Update terms of single variation product when parent product is updated
	 * 
	 * @param int $post_id
	 * @param mixed $post
	 * @param bool $update
	 * @return void
	 */
	public function wvasp_update_on_product_update( $post_id, $post = null, $update = false ) {
		if ( 'product' !== get_post_type( $post_id ) ) {
			return;
		}

		$product = wc_get_product( $post_id );

		// Update terms of single variation product when parent product is updated
		if ( 'variable' === $product->get_type() ) {
			$this->wvasp_terms_update_single_product( $post_id );
		}
	}

	/**
	 * Copy variable product terms to single variation 
	 */
	public function wvasp_terms_update() {
		$product_ids = wc_get_products( array(
			'type'   => 'variable',
			'limit'  => -1,
			'return' => 'ids',
		) );

		// Loop through all variable products
		foreach ( $product_ids as $product_id ) {
			$this->wvasp_terms_update_single_product( $product_id );
		}
	}

	/**
	 * Update single variation product terms
	 */
	public function wvasp_terms_update_single_product( $product_id ) {
		$product = wc_get_product( $product_id );

		// Get all terms of the variable product
		$terms = array(
			'product_cat' => wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) ),
			'product_tag' => wp_get_post_terms( $product->get_id(), 'product_tag', array( 'fields' => 'ids' ) ),
		);

		// Loop through all variations of the variable product
		foreach ( $product->get_children() as $variation_id ) {
			// Loop through all terms of the variable product and update terms
			foreach ( $terms as $taxonomy => $term_ids ) {
				wp_set_post_terms( $variation_id, $term_ids, $taxonomy, false );
			}
		}
	}
}
