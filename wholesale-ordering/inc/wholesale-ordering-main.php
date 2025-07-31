<?php
/**
 * Main Wholesale_Ordering Main Class
 *
 * @version 1.1.0
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Wholesale_Ordering_Main' ) ) :

class Wholesale_Ordering_Main {

	private $whitelist = array('127.0.0.1', '::1');

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 *
	*/
	function __construct() {

		add_action( 'wp_head', array( $this, 'wp_head'), 9999 );
		add_action( 'admin_head', array( $this, 'admin_head'), 1 );
		add_action( 'admin_footer', array( $this, 'admin_footer'), 9999 );
		add_action( 'post_submitbox_misc_actions', array( $this, 'whoelsale_product_checkboxes' ), 20 );
		add_action( 'save_post_product', array( $this, 'action_save_post_product'), 10, 3 );
		add_action( 'woocommerce_before_variations_form', array( $this, 'woocommerce_before_variations_form'), 9999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts'), 20 );
		add_action( 'wp_ajax_add_variation_to_cart', array( $this, 'ordering_ajax_add_variation_to_cart') );
		add_action( 'wp_ajax_nopriv_add_variation_to_cart', array( $this, 'ordering_ajax_add_variation_to_cart') );
		add_filter( 'display_post_states', array( $this, 'display_product_states' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_custom_boxes') );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'recalculate_price') );
		add_action( 'wp_loaded', array( $this, 'fix_mini_cart' ), 9999 );
		add_action( 'init', array( $this, 'wholesaler_account') );
		add_action( 'admin_init', array( $this, 'admin_init') );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts'), 100 );
		add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts'), 100 );
		add_action( 'template_redirect', array( $this, 'remove_product_from_cart_programmatically') );
		
	}

	/**
	 * wholesaler_account
	 *
	 * @version 1.0.0
	 *
	*/
	function wholesaler_account() {

		$labels = array(
			'name'                  => _x( 'Wholesaler Accounts', 'Post Type General Name', 'wholesale-ordering' ),
			'singular_name'         => _x( 'Wholesaler Account', 'Post Type Singular Name', 'wholesale-ordering' ),
			'menu_name'             => __( 'Wholesale Users', 'wholesale-ordering' ),
			'name_admin_bar'        => __( 'Wholesale Users', 'wholesale-ordering' ),
			'archives'              => __( 'User Archives', 'wholesale-ordering' ),
			'attributes'            => __( 'User Attributes', 'wholesale-ordering' ),
			'parent_item_colon'     => __( 'Parent Item:', 'wholesale-ordering' ),
			'all_items'             => __( 'All Users', 'wholesale-ordering' ),
			'add_new_item'          => __( 'Add New User', 'wholesale-ordering' ),
			'add_new'               => __( 'Add New', 'wholesale-ordering' ),
			'new_item'              => __( 'New User', 'wholesale-ordering' ),
			'edit_item'             => __( 'Edit User', 'wholesale-ordering' ),
			'update_item'           => __( 'Update User', 'wholesale-ordering' ),
			'view_item'             => __( 'View User', 'wholesale-ordering' ),
			'view_items'            => __( 'View Users', 'wholesale-ordering' ),
			'search_items'          => __( 'Search User', 'wholesale-ordering' ),
			'not_found'             => __( 'Not found', 'wholesale-ordering' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wholesale-ordering' ),
			'featured_image'        => __( 'Featured Image', 'wholesale-ordering' ),
			'set_featured_image'    => __( 'Set featured image', 'wholesale-ordering' ),
			'remove_featured_image' => __( 'Remove featured image', 'wholesale-ordering' ),
			'use_featured_image'    => __( 'Use as featured image', 'wholesale-ordering' ),
			'insert_into_item'      => __( 'Insert into User', 'wholesale-ordering' ),
			'uploaded_to_this_item' => __( 'Uploaded to this User', 'wholesale-ordering' ),
			'items_list'            => __( 'Users list', 'wholesale-ordering' ),
			'items_list_navigation' => __( 'Users list navigation', 'wholesale-ordering' ),
			'supports' => array('title'),
			'filter_items_list'     => __( 'Filter Users list', 'wholesale-ordering' ),
		);
		$args = array(
			'label'                 => __( 'Wholesaler Account', 'wholesale-ordering' ),
			'description'           => __( 'Wholesaler Accounts', 'wholesale-ordering' ),
			'labels'                => $labels,
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => false,
			'menu_position'         => 80,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
		);
		register_post_type( 'wholesaler_account', $args );
		remove_post_type_support( 'wholesaler_account', 'editor' );

	}

	/**
	 * wp_head.
	 *
	 * @version 1.0.0
	 *
	*/
	function wp_head() {
		if ( in_array( $_SERVER['REMOTE_ADDR'], $this->whitelist ) ):
	?>
		<style>
			.woocommerce div.product div.woocommerce-product-gallery {display:none;}
			.summary.entry-summary, #primary {width: 100% !important; max-width: 100%;}
		</style>
	<?php
		endif;
	}

	/**
	 * admin_head.
	 *
	 * @version 1.0.0
	 *
	*/
	function admin_head() {
	?>
		<style>#admin_head{display:none;}</style>
	<?php
	}

	/**
	 * admin_footer.
	 *
	 * @version 1.0.0
	 *
	*/
	function admin_footer() {
	?>
		<script>
			jQuery(document).ready(function ($) {

				$('#wholesale-product-cb, #wholesale-discount-disable-cb').change(function(){
					showHideDiscountPricesBox();
				});

				showHideDiscountPricesBox();
				function showHideDiscountPricesBox() {

					if( $('#wholesale-product-cb').is(':checked') && !$('#wholesale-discount-disable-cb').is(':checked') ) {
						$('#product_discount_prices_box').show();
					} else {
						$('#product_discount_prices_box').hide();
					}
				}
				
			});
		</script>
	<?php
	}

	/**
	 * whoelsale_product_checkboxes
	 *
	 * @version 1.0.0
	 *
	*/
	function whoelsale_product_checkboxes() {
		global $post;
		if ( $post->post_type !== 'product' ) return;
	?>
		<div class="misc-pub-section" id="wholesale-product-cb-wrapper">
			<label for="wholesale-product-cb"><input type="checkbox" id="wholesale-product-cb" name="is_wholesale_product" value="1" <?php checked( 1, (int) get_post_meta( $post->ID, 'is_wholesale_product', true) ); ?>><?php _e( 'Wholesale Product', 'wholesale-ordering' ); ?></label>
		</div>
		<div class="misc-pub-section" id="dis-wholesale-product-cb-wrapper">
			<label for="wholesale-discount-disable-cb"><input type="checkbox" id="wholesale-discount-disable-cb" name="disable_wholesale_discount" value="1" <?php checked( 1, (int) get_post_meta( $post->ID, 'disable_wholesale_discount', true) ); ?>><?php _e( 'Disable Wholesale Disount', 'wholesale-ordering' ); ?></label>
		</div>
	<?php
	}

	/**
	 * action_save_post_product
	 *
	 * @version 1.0.0
	 *
	*/
	function action_save_post_product( $post_id, $post, $update ) {
		// Checking that is not an autosave
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) return;

		if(!empty($_POST['is_wholesale_product'])) {
			update_post_meta( $post_id, 'is_wholesale_product', '1' );
		} else {
			delete_post_meta( $post_id, 'is_wholesale_product' );
		}
		
		if(!empty($_POST['disable_wholesale_discount'])) {
			update_post_meta( $post_id, 'disable_wholesale_discount', '1' );
		} else {
			delete_post_meta( $post_id, 'disable_wholesale_discount' );
		}

		if( isset( $_POST['discount_tier_1'] ) ) {
			update_post_meta( $post_id, 'discount_tier_1', esc_attr( $_POST['discount_tier_1'] ) );
		} 
		if( isset( $_POST['discount_tier_2'] ) ) {
			update_post_meta( $post_id, 'discount_tier_2', esc_attr( $_POST['discount_tier_2'] ) );
		} 
		if( isset( $_POST['discount_tier_3'] ) ) {
			update_post_meta( $post_id, 'discount_tier_3', esc_attr( $_POST['discount_tier_3'] ) );
		} 
		if( isset( $_POST['discount_tier_4'] ) ) {
			update_post_meta( $post_id, 'discount_tier_4', esc_attr( $_POST['discount_tier_4'] ) );
		}

	}

	/**
	 * is_wholesale_product_category
	 *
	 * @version 1.0.0
	 *
	*/
	public static function is_wholesale_product_category( $product_id ) {
		$return = false;
		$terms = get_the_terms($product_id, 'product_cat');

		if($terms) {
			foreach ($terms as $term) {
				if($term->slug == 'wholesale') {
					$return = true;
					break;
				}
			}
		}

		return $return;
	}
	
	/**
	 * is_wholesale_product
	 *
	 * @version 1.0.0
	 *
	*/
	function is_wholesale_product( $product_id ) {
		$wholesale_product = (int) get_post_meta( $product_id, 'is_wholesale_product', true);
		return $wholesale_product;
	}

	/**
	 * woocommerce_before_variations_form
	 *
	 * @version 1.0.0
	 *
	*/
	function woocommerce_before_variations_form() {
		
		global $product;
		$product_id = absint( $product->get_id() );
		$product_variations = array();

		// IF Wholesale Product
		$attributes = $product->get_attributes();
		if ( $this->is_wholesale_product($product_id) === 1 && $product->is_type( 'variable' ) && $attributes && !empty( $product->get_available_variations() ) ) {

			$taxonomy_terms = $sizes = $colors = array();

			foreach ( $attributes as $attribute ) {

				// If not variation then continue
				if ( !$attribute->get_variation() ) {
					continue;
				}
				$attribute_name = $attribute->get_name();

				// If attribute is taxonomy
				if ( $attribute->is_taxonomy() ) {
					$term_from_query = get_terms( array(
						'taxonomy' => $attribute_name,
						'hide_empty' => true,
						'orderby' => 'meta_value_num',
						'meta_key' => 'order',
						'order' => 'ASC',
					) );
					$term_from_posts = wp_get_post_terms( $product->get_id(), $attribute_name, 'all' );
					
					foreach ($term_from_posts as $term_key => $term_data) {
						$taxonomy_terms[$attribute_name] = $term_from_posts;
					}

					// if($term_from_query && $term_from_posts) {
					// 	foreach ($term_from_query as $term_key => $term_data) {
					// 		$key = $this->findArrayKey($term_from_posts, $term_data);
					// 		if( $key === false ) {
					// 			unset($term_from_query[$term_key]);
					// 		}
					// 	}
					// 	$taxonomy_terms[$attribute_name] = $term_from_query;
					// }

				} else {

					if($attribute->get_visible() == true && $attribute->get_variation() == true) {
						
						$temp_term_data = array();
						foreach ($attribute->get_options() as $option_key => $option) {
							$temp_term = (object) array(
								'name' => $option,
								'slug' => $option,
								'name' => $option,
								// 'taxonomy' => ( strpos($attribute->get_name(), 'pa_') !== false ? $attribute->get_name() : 'pa_' . $attribute->get_name() ),
								'taxonomy' => strtolower($attribute->get_name()),
							);
							$temp_term_data[$option_key] = $temp_term;
						}
						$taxonomy_terms[$attribute->get_name()] = $temp_term_data;

					}

				}

			}


		// IF Wholesale Product
		// if($this->is_wholesale_product($product_id) === 1 && $product->is_type( 'variable' ) && !empty( wc_get_attribute_taxonomies() ) && !empty( $product->get_available_variations() ) && !empty($product->get_variation_attributes() ) ) {

			// $available_attributes = $product->get_variation_attributes(); // get all attributes by variations

			// $taxonomy_terms = $sizes = $colors = array();
			// foreach (wc_get_attribute_taxonomies() as $tax) :
			// 	$attribute_name = $tax->attribute_name;
			// 	if (taxonomy_exists(wc_attribute_taxonomy_name($attribute_name))) :
			// 		if( in_array( 'pa_'.$attribute_name, array_keys($available_attributes) ) ) :
			// 			$terms_args = array(
			// 				'taxonomy' => wc_attribute_taxonomy_name($attribute_name),
			// 				'hide_empty' => true,
			// 				'orderby' => 'meta_value_num',
			// 				'meta_key' => 'order',
			// 				'order' => 'ASC',
			// 			);
			// 			$term = get_terms( $terms_args );
			// 			if($term) {
			// 				foreach ($term as $term_key => $term_data) {
			// 					if( ! in_array( $term_data->slug, $available_attributes['pa_'.$attribute_name] ) ) {
			// 						unset($term[$term_key]);
			// 					}
			// 				}
			// 				$taxonomy_terms[$attribute_name] = $term;
			// 			}
			// 		endif;
			// 	endif;
			// endforeach;

			if( !empty($taxonomy_terms) ) {
				foreach (array_keys($taxonomy_terms) as $taxonomy_term_name) {
					if (strpos(strtolower($taxonomy_term_name), 'color') !== false) {
						$colors = $taxonomy_terms[$taxonomy_term_name];
					} else if (strpos(strtolower($taxonomy_term_name), 'size') !== false) {
						$sizes = $taxonomy_terms[$taxonomy_term_name];
					}
				}
			}

			foreach ($product->get_available_variations() as $product_variation) {
				$product_variations[$product_variation['variation_id']] = $product_variation;
			}

		?>
			<style>
				.variations_form table.variations, .variations_form .quantity, .woocommerce-variation-add-to-cart.variations_button, .woocommerce-variation-add-to-cart {
					display:none !important;
				}
				.wholesale-ordering-table-wrapper .variations { display:none !important; }
				table.variations {
					width:100%  !important;
					table-layout:fixed;
					min-width:500px;
				}
				table.variations tr {  display:inline; float:left }
				table.variations td.value {
    				min-width: 250px;
				}
				table.variations .kt-variation-label { float:left; display: inline-block !important; margin-right:20px; }
				table.variations select {  float:left; display:inline-block;  padding-left:20px; min-width:auto !important; }
				.wh-text-center {
					text-align: center;
				}
				.wh-text-left {
					text-align: left;
				}
				.wholesale_multiple_data {
					width: 100% !important;
				}
				.wholesale_multiple_data th, .wholesale_multiple_data td {
					border: none !important;
					border-top: 1px solid #ddd !important;
					padding: 12px 10px !important;
				}
				.wholesale_multiple_data tfoot tr:last-child th {
					border-bottom: 1px solid #ddd !important;
				}
				.vartable_gc_wrap {
					clear: both;
					overflow: auto;
					margin-bottom: 10px;
				}
				.woocommerce-main-image-thumb { display:none !important }
				.woocommerce div.product p.stock { line-height:0 }
				.globalcartbtn {
					float: right;
					cursor: pointer;
					clear: right;
					overflow-y: auto;
					zoom: 1;
				}
				.added2cartglobal {
					color: #449D44;
					display: none;
					float: right;
					margin-right: 5px;
					margin-top: 8px;
				}
				.wholesale-ordering-table-wrapper {
					overflow-x: auto;
				}
				.qty_parent .order_quantity {
					min-width: 74px !important;
				}

				@-webkit-keyframes spinnerRotate {
					from {
						-webkit-transform:rotate(0deg);
					-webkit-transform-origin: 50% 50%;
					-moz-transform-origin: 50% 50%;
					-ms-transform-origin: 50% 50%;
					-o-transform-origin: 50% 50%;
					transform-origin: 50% 50%;
					}
					to {
						-webkit-transform:rotate(360deg);
					-webkit-transform-origin: 50% 50%;
					-moz-transform-origin: 50% 50%;
					-ms-transform-origin: 50% 50%;
					-o-transform-origin: 50% 50%;
					transform-origin: 50% 50%;
					}
				}
			</style>
			<div class="wholesale-ordering-table-wrapper">
				<table class="wholesale_multiple_data" cellspacing="0" role="presentation">
					<thead>
						<tr>
							<th class="wh-text-left"><?php _e( 'Size', 'wholesale-ordering' ); ?></th>
							<?php foreach( $sizes as $size ): ?>
								<th class="wh-text-center"><label><span><?php echo $size->name; ?></span></label></th>
							<?php endforeach; ?>
						</tr>
						<tr>
							<td class="wh-text-left"><?php echo __( 'Price: ', 'wholesale-ordering' ) . get_woocommerce_currency_symbol(); ?></td>
							<?php foreach( $sizes as $size ): ?>
								<td class="wh-text-center">
									<?php 
										$attributes = array();
										$var_price = 0;
										$attributes['attribute_'.$size->taxonomy] = $size->slug;
										$variation = $this->get_varation_id($product_variations, $attributes);
										if($variation) {
											$var_price = $variation['display_price'];
										}
										echo wc_price($var_price);
									?>
								</td>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach( $colors as $color ):
							$color_code = get_term_meta( $color->term_id, 'term_color', true );
							$color_code = ( ! empty( $color_code ) ) ? "#$color_code" : '#ffffff';
						?>
							<tr>
								<td>
									<div style="display: flex;align-items: center;">
										<span style="flex: 1 0 30px;max-width: 30px;width: 30px;height: 30px;background-color: <?php echo $color_code; ?>;border: 1px solid #ddd;border-radius:0;margin-right: 10px;">&nbsp;</span>
										<label style="line-height: 1.2;"><span><?php echo $color->name; ?></span></label>
									</div>
								</td>
								<?php foreach( $sizes as $size ):
									$attributes = array();
									$attributes['attribute_'.$size->taxonomy] = $size->slug;
									$attributes['attribute_'.$color->taxonomy] = $color->slug;
									$variation = $this->get_varation_id($product_variations, $attributes);
								?>
									<td class="qty_parent">
										<?php if( $variation != false && $variation['is_in_stock'] == true && $variation['is_purchasable'] == true && $variation['variation_is_active'] == true && $variation['variation_is_visible'] == true ):
										?>
											<input type="hidden" class="variation_id" value="<?php echo $variation['variation_id']; ?>">
											<input type="hidden" class="price" value="<?php echo $variation['display_price']; ?>">
											<input type="hidden" class="form_vartable_attribute_array" name="form_vartable_attribute_array" value='<?php echo json_encode($variation['attributes']); ?>'>
											<input type="number" class="input-text qty text order_quantity" name="order_quantity" step="1" min="0" max="<?php echo $variation['max_qty']; ?>" pattern="[0-9]" onkeypress="return !(event.charCode == 46 || event.charCode == 45 || event.charCode == 43 || event.charCode == 101)">
										<?php else: ?>
											<div class="wh-text-center">—</div>
										<?php endif; ?>
										<div class="wh-text-center"><p class="stock-availability"><?php echo str_replace('in stock','',$variation['availability_html']); ?></p></div>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr>
							<th></th>
							<?php foreach( $sizes as $size ): ?>
								<th class="wh-text-center"><label for="<?php echo 'attribute_' . $size->taxonomy . $size->slug; ?>"><span><?php echo $size->name; ?></span></label></th>
							<?php endforeach; ?>
						</tr>
					</tfoot>
				</table>
				<br>
				<div class="vartable_gc_wrap">
					<a href="#globalcartbtn" class="globalcartbtn button alt disabled" data-product_id="<?php echo $product_id; ?>"><?php _e('Add to cart', 'wholesale-ordering'); ?></a>
				</div>
			</div>
		<?php
		}

	}

	/**
	 * get_varation_id
	 *
	 * @version 1.0.0
	 *
	*/
	function get_varation_id( $product_variations, $attributes ) {

		foreach ($product_variations as $product_variation) {
			if(isset($product_variation['attributes'])) {
				$found = false;
				$attribute_count = 0;

				foreach ($attributes as $attribute_key => $attribute_val) {
					if( isset( $product_variation['attributes'][$attribute_key] ) && $product_variation['attributes'][$attribute_key] == $attribute_val ) {
						$found = ($attribute_count === 0 ? true : (!$found ? false : true ) );
					} else {
						$found = false;
					}
					$attribute_count++;
				}

				if($found) {
					return $product_variation;
					break;
				}
			}
		}

		return false;

	}

	/**
	 * wp_enqueue_scripts
	 *
	 * @version 1.0.0
	 *
	*/
	function wp_enqueue_scripts() {
		// If product page 
		if ( function_exists('is_product') && is_product() ) {
			global $post;
			$product_id = $post->ID;
			$product = wc_get_product( $product_id );

			if($this->is_wholesale_product($product_id) === 1 && $product->is_type( 'variable' ) ) {
				wp_enqueue_script('wholesale_order_js', plugins_url('js/add-to-cart.js', __DIR__) , array( 'jquery' ) , '1.0.0', true );

				$vars = array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'cart_url' => wc_get_cart_url(),
					'vartable_ajax' => 1,
					'currency_symbol' => get_woocommerce_currency_symbol(),
					'thousand_separator' => wc_get_price_thousand_separator(),
					'decimal_separator' => wc_get_price_decimal_separator(),
					'decimal_decimals' => wc_get_price_decimals(),
					'currency_pos' => get_option('woocommerce_currency_pos'),
					'price_display_suffix' => get_option('woocommerce_price_display_suffix'),
				);
			
				wp_localize_script('wholesale_order_js', 'localvars', $vars);
			}

		}
	}

	/**
	 * ordering_ajax_add_variation_to_cart
	 *
	 * @version 1.0.0
	 *
	*/
	function ordering_ajax_add_variation_to_cart() {

		ob_start();
	
		$product_id = absint($_POST['product_id']);
		$quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
	
		$variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : '';
		// $variations = isset($_POST['variations']) ? json_decode(str_replace('||||||', '\"', stripslashes($_POST['variations'])) , true) : '';
			
		
		if ( WC()->cart->add_to_cart($product_id, $quantity, $variation_id, wc_get_product_variation_attributes( $variation_id )) ) {
	
			do_action('woocommerce_set_cart_cookies', TRUE);
	
			if (get_option('woocommerce_cart_redirect_after_add') == 'yes') {
				wc_add_to_cart_message(array(
					$product_id => $quantity
				) , true);
				
			}
	
			// Return fragments
			WC_AJAX::get_refreshed_fragments();
	
		} else {
	
			// If there was an error adding to the cart, redirect to the product page to show any errors
			
			$error_messages = false;
			if (wc_notice_count('error') > 0) {
				$error_notices = wc_get_notices( 'error' );
				if ( !empty( $error_notices ) ) {
					foreach( $error_notices as $error_notice ) {
						$error_messages[] = $error_notice[ 'notice' ];
					}
				}
			}
			wc_clear_notices();
			
			$data = array(
				'error' => true,
				'error_message' => implode( '<br />', $error_messages ),
				'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id) , $product_id)
			);
	
			wp_send_json($data);
	
		}
	
		die();
	}

	/**
	 * display_product_states
	 * this fills in the columns that were created with each individual post's value
	 * 
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 */
	public function display_product_states( $post_states, $post ) {

		if($post->post_type === 'product') {
			if ( $this->is_wholesale_product($post->ID) === 1 ) {
				$post_states['wholesale_product'] = __( 'Wholesale Product', 'wholesale-ordering' );
			}
		}

		return $post_states;
	}

	/**
	 * add_custom_boxes
	 * 
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 */
	function add_custom_boxes() {
		add_meta_box( 'product_discount_prices_box', __( 'Wholesaler prices per Pieces', 'wholesale-ordering' ), array( $this, 'product_discount_prices_box_callback'), 'product', 'normal', 'high' );
	}

	/**
	 * product_discount_prices_box_callback
	 * 
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 */
	function product_discount_prices_box_callback( $post ) {
		$discount_tier_1 = esc_attr(get_post_meta( $post->ID, 'discount_tier_1', true));
		$discount_tier_2 = esc_attr(get_post_meta( $post->ID, 'discount_tier_2', true));
		$discount_tier_3 = esc_attr(get_post_meta( $post->ID, 'discount_tier_3', true));
		$discount_tier_4 = esc_attr(get_post_meta( $post->ID, 'discount_tier_4', true));
	?>
	<div>
		<div style="margin-bottom:8px;">
			<label for="for_1-35" style="display:block; width:100%;font-weight: 700;">1-35</label>
			<input id="for_1-35" style="display:block; width:100%;" type="number" min="0" step="any" name="discount_tier_1" value="<?php echo $discount_tier_1; ?>">
		</div>
		<div style="margin-bottom:8px;">
			<label for="for_36-99" style="display:block; width:100%;font-weight: 700;">36-99</label>
			<input id="for_36-99" style="display:block; width:100%;" type="number" min="0" step="any" name="discount_tier_2" value="<?php echo $discount_tier_2; ?>">
		</div>
		<div style="margin-bottom:8px;">
			<label for="for_100-499" style="display:block; width:100%;font-weight: 700;">100-499</label>
			<input id="for_100-499" style="display:block; width:100%;" type="number" min="0" step="any" name="discount_tier_3" value="<?php echo $discount_tier_3; ?>">
		</div>
		<div style="margin-bottom:8px;">
			<label for="for_500-1000" style="display:block; width:100%;font-weight: 700;">500-1000</label>
			<input id="for_500-1000" style="display:block; width:100%;" type="number" min="0" step="any" name="discount_tier_4" value="<?php echo $discount_tier_4; ?>">
		</div>
	</div>
	
	<?php
	}

	/**
	 * product_discount_prices_box_callback
	 * 
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 */
	function recalculate_price( $cart_object ) {

		$discounts = array();
		
		foreach ( $cart_object->get_cart() as $value ) {

			$product_id = (int) $value['product_id'];
			$variation_id = (int) $value['variation_id'];
			$quantity = (int) $value['quantity'];

			if( 
				!empty($variation_id) // If variation id not empty (Variable product)
				&& $this->is_wholesale_product($product_id) === 1 // If wholesale product
				&& get_post_meta( $product_id, 'disable_wholesale_discount', true) != '1' // If discounts not disabled
			) {
				if( isset( $discounts[$product_id] ) ) {
					$discounts[$product_id] += $quantity;
				} else {
					$discounts[$product_id] = $quantity;
				}
			}
		}

		// Discounts needs to apply to these products 
		foreach ($discounts as $discount_product_id => $item_quantity) {

			$discount_price = 0;
			if( $item_quantity <= 35 ) {
				$discount_price = esc_attr(get_post_meta( $discount_product_id, 'discount_tier_1', true));
			} else if( $item_quantity > 35 && $item_quantity <= 99 ) {
				$discount_price = esc_attr(get_post_meta( $discount_product_id, 'discount_tier_2', true));
			} else if( $item_quantity > 99 && $item_quantity <= 499 ) {
				$discount_price = esc_attr(get_post_meta( $discount_product_id, 'discount_tier_3', true));
			} else if( $item_quantity > 499 && $item_quantity <= 1000 ) {
				$discount_price = esc_attr(get_post_meta( $discount_product_id, 'discount_tier_4', true));
			}

			$discounts[$discount_product_id] = $discount_price;

		}

		foreach ( $cart_object->get_cart() as $value ) {

			$product_id = (int) $value['product_id'];
			$variation_id = (int) $value['variation_id'];

			if( 
				!empty($variation_id) // If variation id not empty (Variable product)
				&& $this->is_wholesale_product($product_id) === 1 // If wholesale product
				&& get_post_meta( $product_id, 'disable_wholesale_discount', true) != '1' // If discounts not disabled
			) {

				if( isset( $discounts[$product_id] ) && floatval($discounts[$product_id]) > 0 ) {
					$value[ 'data' ]->set_price( floatval($discounts[$product_id]) );
				}
			}
		}
	
	}

	/**
	 * is_frontend()
	 *
	 * @version 1.0.5
	 * @since   1.0.5
	 * 
	 * @return  boolean
	 * 
	 */
	function is_frontend() {
		if ( !is_admin() ) {
			return true;
		} elseif ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return ( ! isset( $_REQUEST['action'] ) || ! is_string( $_REQUEST['action'] ) || ! in_array( $_REQUEST['action'], array( 'woocommerce_load_variations' ) ) );
		} else {
			return false;
		}
	}

	/**
	 * fix_mini_cart
	 * Woocommerce Mini cart prices fix
	 * 
	 * Temporary fix
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 * 
	 */
	function fix_mini_cart() {
		if ( $this->is_frontend() && function_exists( 'WC' ) && null !== WC() && isset( WC()->cart ) && is_object( WC()->cart ) && method_exists( WC()->cart, 'calculate_totals' ) ) {
			WC()->cart->calculate_totals();
		}
	}

	/**
	 * findArrayKey
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 * 
	 */
	function findArrayKey($array, $find) {

		foreach ( $array as $array_key => $element ) {
			if($element->term_id == $find->term_id) {
				return $element;
			}
		}

		return false;
	}

	/**
	 * admin_init
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 * 
	 */
	function admin_init() {
		if (
			in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) // IF woocommerce plugin active
		) { 
			$terms = wc_get_attribute_taxonomies();
	
			foreach ($terms as $term) {
				$get_term = wc_get_attribute( $term->attribute_id );
				
				if($get_term && strpos($get_term->slug, 'color') !== false) { // If name has color word
					
					$term_slug = $get_term->slug;

					add_action( $term_slug . '_add_form_fields', array($this, 'color_picker_add_custom_fields'), 50 );
					add_action( $term_slug . '_edit_form_fields', array($this, 'color_picker_edit_custom_fields'), 50 );
					add_action( 'created_' . $term_slug, array($this, 'wet_helper_save_termmeta') );
					add_action( 'edited_' . $term_slug, array($this, 'wet_helper_save_termmeta') );
					add_filter( 'manage_edit-' . $term_slug . '_columns', array($this, 'custom_column_in_taxonomy_term_list') );
					add_filter( 'manage_' . $term_slug . '_custom_column', array($this, 'custom_column_in_taxonomy_term_list_data'), 10, 3 );

				}
			}
	
		}	
	}

	/**
	 * color_picker_add_custom_fields
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 * 
	 */
	function color_picker_add_custom_fields($term) {
	?>
		<div class="form-field form-required term-term_color-wrap">
			<label for="term_color"><?php _e('Color', 'wholesale-ordering'); ?></label>
			<input type="text" id="term_color" name="term_color" class="weteffect-colorpicker" value="#ffffff">
		</div>
	<?php
	}

	/**
	 * color_picker_edit_custom_fields
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 * 
	 */
	function color_picker_edit_custom_fields($term) {
		$color = get_term_meta( $term->term_id, 'term_color', true );
		$color = ( ! empty( $color ) ) ? "#$color" : '#ffffff';
	?>
		<tr class="form-field term-term_color-wrap">
			<th scope="row"><label for="term_color"><?php _e('Color', 'wholesale-ordering'); ?></label></th>
			<td>
				<input type="text" id="term_color" name="term_color" class="weteffect-colorpicker" value="<?php echo $color; ?>" />
			</td>
		</tr>
	<?php
	}

	/**
	 * wet_helper_save_termmeta
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 * 
	 */
	function wet_helper_save_termmeta( $term_id ) {
		// Save term color if possible
		if( isset( $_POST['term_color'] ) && ! empty( $_POST['term_color'] ) ) {
			update_term_meta( $term_id, 'term_color', sanitize_hex_color_no_hash( $_POST['term_color'] ) );
		} else {
			delete_term_meta( $term_id, 'term_color' );
		}
	}

	/**
	 * custom_column_in_taxonomy_term_list
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 * 
	 */
	function custom_column_in_taxonomy_term_list( $columns ) {
		$columns['term_color'] = __('Color', 'wholesale-ordering');
		return $columns;
	}

	/**
	 * custom_column_in_taxonomy_term_list_data
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 * 
	 */
	function custom_column_in_taxonomy_term_list_data( $content, $column_name, $term_id ) {
		$color = get_term_meta( $term_id, 'term_color', true );
	
		switch ( $column_name ) {
			case 'term_color':
					$content = '<span style="display: flex;width: 50px;height: 25px;border-radius: 3px;border: 1px solid #ddd;background-color: #'.(empty($color) ? 'ffffff' : $color ).';">&nbsp;</span>';
				break;
			default:
				break;
		}
	
		return $content;
	}

	/**
	 * admin_enqueue_scripts
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 * 
	 */
	function admin_enqueue_scripts() {

		$screen = get_current_screen();
	
		if($screen && strpos($screen->id, 'color') !== false && isset($screen->taxonomy) && strpos($screen->taxonomy, 'color') !== false ) {
			// Colorpicker Script
			wp_enqueue_script( 'wp-color-picker' );
			// Colorpicker Style
			wp_enqueue_style( 'wp-color-picker' );
		}
	}

	/**
	 * admin_print_scripts
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 * 
	 */
	function admin_print_scripts() {
		$screen = get_current_screen();
	
		if($screen && strpos($screen->id, 'color') !== false && isset($screen->taxonomy) && strpos($screen->taxonomy, 'color') !== false ):
	?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$( '.weteffect-colorpicker' ).wpColorPicker();
			});
		</script>
	<?php
		endif;
	}

	/**
	 * remove_product_from_cart_programmatically
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 * 
	 */
	function remove_product_from_cart_programmatically() {

		if ( is_admin() ) return;
		
		if (
			in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) // IF woocommerce plugin active
		) { 
			
			$wholesale_user = $this->is_wholesale_user();
			
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$product_id = $cart_item['product_id'];
				if (
					( $wholesale_user && !self::is_wholesale_product_category($product_id) )
					|| ( is_user_logged_in() && !$wholesale_user && self::is_wholesale_product_category($product_id) )
				) {
					WC()->cart->remove_cart_item( $cart_item_key );
				}
			}

		}

	}

	/**
	 * is_wholesale_user
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * 
	 * 
	 */
	function is_wholesale_user() {
		$return = false;
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			if ( in_array( 'default_wholesaler', (array) $user->roles ) ) {
				$return = true;
			}
		}

		return $return;
	}
	
}

endif;

return new Wholesale_Ordering_Main();