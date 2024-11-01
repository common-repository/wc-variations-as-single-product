<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://storeplugin.net/plugins/variations-as-single-product-for-woocommerce
 * @since      1.0.0
 *
 * @package    Woo_Variations_As_Single_Product
 * @subpackage Woo_Variations_As_Single_Product/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Woo_Variations_As_Single_Product
 * @subpackage Woo_Variations_As_Single_Product/public
 * @author     StorePlugin <contact@storeplugin.net>
 */
class Woo_Variations_As_Single_Product_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-variations-as-single-product-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-variations-as-single-product-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Shows variation product as single product
	 * 
	 * @param object $query
	 * @return object
	 */
	public function variation_as_single_product ( $query ) {
		$enable_variations_as_product = get_option( 'wvasp_enable_variations_as_product', 'no' );
		$disable_category_page_single_variation = get_option( 'wvasp_disable_category_page_single_variation', 'no' );
		$disable_tag_page_single_variation = get_option( 'wvasp_disable_tag_page_single_variation', 'no' );
		$disable_search_page_single_variation = get_option( 'wvasp_disable_search_page_single_variation', 'no' );

		// Add action to start logic
		do_action( 'wvasp_variation_as_single_product_logic_start', $query );

		if ( $enable_variations_as_product == 'no' ) {
			return $query;
		}

		if ( is_product_category() && $disable_category_page_single_variation == 'yes' ) {
			return $query;
		}

		if ( is_product_tag() && $disable_tag_page_single_variation == 'yes' ) {
			return $query;
		}

		if ( is_search() && $disable_search_page_single_variation == 'yes' ) {
			return $query;
		}

		$query->set( 'post_type', array('product','product_variation') );
		
		// Get exclude ids
		$exclude_ids = $this->execule_product_ids();

		$query->set( 'post__not_in', array_unique($exclude_ids) );

		return $query;
	}

	/**
	 * Shows variation product as single product for shortcodes
	 * 
	 * @param object $query
	 * @return object
	 */
	public function variation_as_single_product_shortcode ( $query ) {
		$enable_variations_as_product = get_option( 'wvasp_enable_variations_as_product', 'no' );
		$disable_category_page_single_variation = get_option( 'wvasp_disable_category_page_single_variation', 'no' );
		$disable_tag_page_single_variation = get_option( 'wvasp_disable_tag_page_single_variation', 'no' );
		$disable_search_page_single_variation = get_option( 'wvasp_disable_search_page_single_variation', 'no' );

		if ( $enable_variations_as_product == 'no' ) {
			return $query;
		}

		if ( is_product_category() && $disable_category_page_single_variation == 'yes' ) {
			return $query;
		}

		if ( is_product_tag() && $disable_tag_page_single_variation == 'yes' ) {
			return $query;
		}

		if ( is_search() && $disable_search_page_single_variation == 'yes' ) {
			return $query;
		}
		
		$query['post_type'] = [ 'product', 'product_variation' ];
		
		// Get exclude ids
		$exclude_ids = $this->execule_product_ids();

		$query['post__not_in'] = $exclude_ids;

		return $query;
	}

	/**
	 * Return exclude product ids
	 * 
	 * @return array $exclude_ids
	 */
	public function execule_product_ids () {
		$hide_parent_products   = get_option( 'wvasp_hide_parent_products', 'no' );
		
		$exclude_category_fields   = get_option( 'wvasp_exclude_category_fields', array() );
		$exclude_tag_fields        = get_option( 'wvasp_exclude_tag_fields', array() );

		$exclude_ids = array();

		// Hide variation product parent product
		if ( $hide_parent_products == 'yes' ) {
			$exclude_ids = array_merge($exclude_ids, $this->hide_parent_variable_products());
		}

		// Exclude category
		if ( !empty($exclude_category_fields) ) {
			$exclude_ids = array_merge($exclude_ids, $this->exclude_category_products($exclude_category_fields));
		}

		// Exclude tag
		if ( !empty($exclude_tag_fields) ) {
			$exclude_ids = array_merge($exclude_ids, $this->exclude_tag_products($exclude_tag_fields));
		}

		// Exclude non published products variation
		$exclude_ids = array_merge($exclude_ids, $this->exclude_non_published_product_variants());

		// Exclude single product from Single product settings
		$exclude_ids = array_merge($exclude_ids, $this->exclude_variation_based_on_product_meta());

		// add pro apply_filters to $exclude_ids
		$exclude_ids = apply_filters( 'woo_variations_as_single_product_exclude_ids', $exclude_ids );

		return $exclude_ids;
	}

	/**
	 * Hide parent product
	 * 
	 * @return array $variable_product_parent_ids Parent product ids
	 */
	public function hide_parent_variable_products () {
		$args = array(
			'type' => 'variable',
			'limit' => -1,
			'return' => 'ids',
		);
		$variable_product_parent_ids = wc_get_products( $args );

		// Ignore Parent product ID from hidden for setected categories and tags ->
		// when "Exclude Product Categories" and "Exclude Product Tags" is enabled
		$exclude_category_fields = get_option( 'wvasp_exclude_category_fields', array() );
		$exclude_tag_fields		 = get_option( 'wvasp_exclude_tag_fields', array() );

		$args = array(
			'type' => 'variable',
			'limit' => -1,
			'return' => 'ids',
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $exclude_category_fields,
				),
			)
		);
		$variable_product_category_parent_ids = wc_get_products( $args );

		$args = array(
			'type' => 'variable',
			'limit' => -1,
			'return' => 'ids',
			'tax_query' => array(
				array(
					'taxonomy' => 'product_tag',
					'field'    => 'term_id',
					'terms'    => $exclude_tag_fields,
				),
			)
		);
		$variable_product_tag_parent_ids = wc_get_products( $args );

		$category_tags_parent_ids = array_merge($variable_product_category_parent_ids, $variable_product_tag_parent_ids);
		$variable_product_parent_ids = array_diff($variable_product_parent_ids, $category_tags_parent_ids);

		return $variable_product_parent_ids;
	}

	/**
	 * Exclude category products
	 * 
	 * @param array $exclude_category_fields
	 * @return array $variable_product_child_ids Child product ids of excluded category
	 */
	public function exclude_category_products ( $exclude_category_fields ) {
		$exclude_child_category_fields   = get_option( 'wvasp_exclude_child_category_fields', 'no' );
		//print_r($exclude_category_fields);
		$args = array(
			'type' => 'variable',
			'limit' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $exclude_category_fields,
				),
			)
		);

		if( $exclude_child_category_fields == 'yes' ) {
			$args['tax_query'][0]['include_children'] = false;
		}

		$variable_product_parent_ids = wc_get_products( $args );

		$variable_product_child_ids = array();

		// Get child products of parent products
		foreach ($variable_product_parent_ids as $variable_product_parent_id) {
			$variable_product_child_ids = array_merge($variable_product_child_ids, $variable_product_parent_id->get_children());
		}

		return $variable_product_child_ids;
	}

	/**
	 * Exclude tag products
	 * 
	 * @param array $exclude_tag_fields
	 * @return array $variable_product_child_ids Child product ids of excluded tag
	 */
	public function exclude_tag_products ( $exclude_tag_fields ) {
		$args = array(
			'type' => 'variable',
			'limit' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'product_tag',
					'field'    => 'term_id',
					'terms'    => $exclude_tag_fields,
				),
			)
		);

		$variable_product_parent_ids = wc_get_products( $args );

		$variable_product_child_ids = array();

		// Get child products of parent products
		foreach ($variable_product_parent_ids as $variable_product_parent_id) {
			$variable_product_child_ids = array_merge($variable_product_child_ids, $variable_product_parent_id->get_children());
		}

		return $variable_product_child_ids;
	}

	/**
	 * Exclude non published product variants
	 * 
	 * @return array $exclude_ids
	 */
	public function exclude_non_published_product_variants () {
		global $wpdb;

		// Query to get all variation IDs where the parent product is not published
		$non_published_variation_ids = $wpdb->get_col("
			SELECT p.ID 
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->posts} parent_p ON p.post_parent = parent_p.ID
			WHERE p.post_type = 'product_variation'
			AND parent_p.post_status != 'publish'
		");

		return $non_published_variation_ids;
	}

	/**
	 * Exclude single product
	 * 
	 * @return array $exclude_ids
	 */
	public function exclude_variation_based_on_product_meta () {
		global $wpdb;
		$exclude_ids = array();
		
		// Get product ID's based on Single Product meta '_wvasp_single_exclude_varations'
		$get_excluded_product_ids = $wpdb->get_col("
			SELECT pm.post_id 
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.post_type = 'product'
			AND pm.meta_key = '_wvasp_single_exclude_varations'
			AND pm.meta_value = 'yes'
		");

		// Initialize array to store child IDs
		$get_excluded_product_child_ids = array();

		if (!empty($get_excluded_product_ids)) {
			// Prepare placeholders for the IN clause
			$placeholders = implode(',', array_fill(0, count($get_excluded_product_ids), '%d'));

			// Fetch all child (variation) IDs of the excluded parent products
			$get_excluded_product_child_ids = $wpdb->get_col($wpdb->prepare("
				SELECT p.ID 
				FROM {$wpdb->posts} p
				WHERE p.post_parent IN ($placeholders)
				AND p.post_type = 'product_variation'
			", ...$get_excluded_product_ids));
		}

		// Get variation ID's based on Single Product variation meta '_wvasp_single_exclude_variation'
		$get_excluded_variant_ids = $wpdb->get_col("
			SELECT pm.post_id 
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.post_type = 'product_variation'
			AND pm.meta_key = '_wvasp_single_exclude_variation'
			AND pm.meta_value = 'yes'
		");

		// Get all product ID's based on Single Product meta '_wvasp_single_hide_parent_product'
		$get_hide_parent_product_ids = $wpdb->get_col("
			SELECT pm.post_id 
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.post_type = 'product'
			AND pm.meta_key = '_wvasp_single_hide_parent_product'
			AND pm.meta_value = 'yes'
		");

		$exclude_ids = array_values( array_unique( array_merge( $get_excluded_product_child_ids, $get_excluded_variant_ids, $get_hide_parent_product_ids ) ) );

		return $exclude_ids;
	}

	/**
	 * Modify variation title
	 * 
	 * @param string $title
	 * @param object $product
	 * @return string $title
	 */
	public function modify_variation_title( $title, $id ) {
		$post_type = get_post_type($id);
		
		if($post_type != "product_variation") {
			return $title;
		}

		$variation_title = get_post_meta( $id, '_wvasp_single_variation_title', true );

		if( !empty($variation_title) ) {
			return $variation_title;
		}
		
		// Don't include filter if pro plugin version is less than 3.3.0
		if ( defined( 'WC_VARIATIONS_AS_SINGLE_PRODUCT_PRO_VERSION' ) && version_compare( WC_VARIATIONS_AS_SINGLE_PRODUCT_PRO_VERSION, '3.3.0', '<' ) ) {
			return $title;
		}

		// Variation Title filter for pro plugin
		$title = apply_filters( 'wvasp_global_variation_title', $title, $id );
		return $title;
	}

	/**
	 * WooCommerce Wholesale Prices Premium plugin support
	 * 
	 * @param object $query
	 * @return object
	 */
	public function woocommerce_wholesale_prices_variation_support( $query ) {
		
		// Return if WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER not define and not WooCommerce product-related pages
		if ( is_admin() || ! ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() || is_search() ) || ! defined( 'WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER' ) ) {
			return;
		}

		$meta_query = (array) $query->get( 'meta_query' );
		if ( empty( array_filter( $meta_query ) ) ) {
			return;
		}    
	
		// Check if there's a meta query with the key matching WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER
		$wholesale_query = array_reduce($meta_query, function($carry, $item) {
			if (isset($item['key']) && WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER === $item['key']) {
				return true;
			}
			return $carry;
		}, false);
	
		// return if not a wholesale query
		if (!$wholesale_query) {
			return;
		}
	
		$post_type = (array) $query->get( 'post_type' );
	
		if( in_array( 'product', $post_type ) && ! in_array( 'product_variation', $post_type ) ) {
			$post_type[] = 'product_variation';
			$query->set( 'post_type', $post_type );
		}
	}
}
