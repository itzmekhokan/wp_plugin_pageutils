<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * PU_Admin class.
 */
class PU_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		// Add menus.
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 10 );
		add_action( 'admin_menu', array( $this, 'fix_admin_menu' ), 999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_head', array( $this, 'admin_head_styles') );
		add_action( 'pu_settings_options_footer', array( $this, 'pu_settings_options_footer') );
		add_action( 'add_meta_boxes', array( $this, 'add_hreflang_tag_meta_box') );
		add_action( 'save_post', array( $this, 'save_hreflang_tag_meta_box') );

		$supported_content_types = hreflang_tags_get_settings('hreflang_tags_content_types');
		$supported_taxonomy = isset($supported_content_types['taxonomy']) ? $supported_content_types['taxonomy'] : array();
		foreach ( $supported_taxonomy as $taxonomy ) {
			add_action( $taxonomy . '_add_form_fields', array( $this, 'add_hreflang_tag_term_meta_box' ) );
			add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_hreflang_tag_term_meta_box' ), 99 );
		}
		add_action( 'create_term', array( $this, 'save_hreflang_tag_term_meta_box'), 99, 3 );
		add_action( 'edit_term', array( $this, 'save_hreflang_tag_term_meta_box'), 99, 3 );
		// filter posts & pages search by url slug
		add_action( 'posts_search', array( $this, 'filter_posts_pages_query' ), 99, 2 );
	}

	/**
	 * Includes files.
	 */
	public function includes() {
		// PU Global settings
		include_once PU_ABSPATH . 'includes/settings/class-pu-admin-settings.php';
		
		// Hreflang tags settings
		include_once PU_ABSPATH . 'includes/settings/class-pu-hreflang-tags-settings.php';
		// star rating settings
		include_once PU_ABSPATH . 'includes/settings/class-pu-star-rating-settings.php';
		// wp svg icons settings
		include_once PU_ABSPATH . 'includes/settings/class-pu-wp-svg-icons-settings.php';
		// gdpr cookie settings
		include_once PU_ABSPATH . 'includes/settings/class-pu-gdpr-cookie-settings.php';
		
		// list tables
		include_once PU_ABSPATH . 'includes/list-tables/class-faq-admin-list-table.php';
		include_once PU_ABSPATH . 'includes/list-tables/class-faq-admin-list-table-qna.php';
	}

	/**
	 * Add menu items.
	 */
	public function admin_menu() {

		add_menu_page( __( 'Page Utils', 'page-utils' ), __( 'Page Utils', 'page-utils' ), 'manage_options', 'page-utils', null, 'dashicons-editor-justify', 60 );
		add_submenu_page( 'page-utils', __( 'FAQ', 'page-utils' ), __( 'FAQ', 'page-utils' ), 'manage_options', 'faq', array( $this, 'faq_list_handler' ) );
		add_submenu_page('page-utils', '', '', 'manage_options', 'faq-qna', array( $this, 'faq_qna_list_handler' ) );
		add_submenu_page( 'page-utils', __( 'Hreflang Tags', 'page-utils' ), __( 'Hreflang Tags', 'page-utils' ), 'manage_options', 'href_lang', array( $this, 'href_lang_handler' ) );
		add_submenu_page( 'page-utils', __( 'Star Rating', 'page-utils' ), __( 'Star Rating', 'page-utils' ), 'manage_options', 'star_rating', array( $this, 'star_rating_handler' ) );
		add_submenu_page( 'page-utils', __( 'WP SVG Icons', 'page-utils' ), __( 'WP SVG Icons', 'page-utils' ), 'manage_options', 'wp_svg_icons', array( $this, 'wp_svg_icons_handler' ) );
		add_submenu_page( 'page-utils', __( 'GDPR Cookie', 'page-utils' ), __( 'GDPR Cookie', 'page-utils' ), 'manage_options', 'gdpr_cookie', array( $this, 'gdpr_cookie_handler' ) );
		add_submenu_page('page-utils', __( 'Settings', 'page-utils' ), __( 'Settings', 'page-utils' ), 'manage_options', 'pu-settings', array( $this, 'pu_settings_handler' ) );
	}

	/**
	 * Hide parent submenu.
	 */
	public function fix_admin_menu(){
		global $submenu;
		
		if ( ! isset( $submenu['page-utils'] ) ) {
			return;
		}
		unset( $submenu['page-utils'][0] );
		
	}


	/**
	 * Filter seaech query SQL
	 */
	public function filter_posts_pages_query( $search, $query ) {
		global $wpdb;

		// check is admin and has search query
		if ( !is_admin() ) return $search;
		if ( !$query->is_search ) return $search;
		if ( $query->is_search && isset( $query->query['s'] ) && empty( $query->query['s'] ) ) return $search;
		// check post_type is post & page
		$post_types = apply_filters( 'pu_enabled_search_by_slug_post_types', array( 'post', 'page' ) );
		if( isset( $query->query['post_type'] ) && !in_array( $query->query['post_type'], $post_types ) ) return $search;

		$search = '';
		$q = $query->query_vars;
		$n                         = ! empty( $q['exact'] ) ? '' : '%';
		$searchand                 = '';
		$q['search_orderby_title'] = array();
		$exclusion_prefix = apply_filters( 'wp_query_search_exclusion_prefix', '-' );
		
		if( $query->query['s'] ) {
			$term = $query->query['s'];
			// If there is an $exclusion_prefix, terms prefixed with it should be excluded.
			$exclude = $exclusion_prefix && ( $exclusion_prefix === substr( $term, 0, 1 ) );
			if ( $exclude ) {
				$like_op  = 'NOT LIKE';
				$andor_op = 'AND';
				$term     = substr( $term, 1 );
			} else {
				$like_op  = 'LIKE';
				$andor_op = 'OR';
			}

			if ( $n && ! $exclude ) {
				$like                        = '%' . $wpdb->esc_like( $term ) . '%';
				$q['search_orderby_title'][] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $like );
			}

			$like      = $n . $wpdb->esc_like( $term ) . $n;
			$search   .= $wpdb->prepare( "{$searchand}(({$wpdb->posts}.post_title $like_op %s) $andor_op ({$wpdb->posts}.post_excerpt $like_op %s) $andor_op ({$wpdb->posts}.post_content $like_op %s) $andor_op ({$wpdb->posts}.post_name $like_op %s))", $like, $like, $like, $like );
			$searchand = ' AND ';
		}

		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";
			if ( ! is_user_logged_in() ) {
				$search .= " AND ({$wpdb->posts}.post_password = '') ";
			}
		}

		return $search;

	}


	/**
	 * Output of backend FAQ list page.
	 */
	public function faq_list_handler() { 
		global $wpdb;
		
    	$faq_headers = new FAQ_Admin_List_Table();
    	$faq_headers->prepare_items();
    	$message = ( isset( $_REQUEST['message'] ) && $_REQUEST['message'] != 0 ) ? absint( $_REQUEST['message'] ) : 0;
    	$referer  = wp_get_referer();
		if ( ! $referer ) { // For POST requests.
			$referer = wp_unslash( $_SERVER['REQUEST_URI'] );
		}
		$referer = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'error', 'message', 'paged' ), $referer );
		$faq_id = ( isset( $_REQUEST['id'] ) ) ? absint( $_REQUEST['id'] ) : 0;
		$class = ( isset( $_REQUEST['error'] ) ) ? 'error' : 'updated';
    	?>

		<div class="wrap nosubsub">
			<h1 class="wp-heading-inline"><?php echo __( 'FAQ', 'page-utils' ); ?></h1>

			<?php
			if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
				/* translators: %s: search keywords */
				printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( wp_unslash( $_REQUEST['s'] ) ) );
			}
			?>

			<hr class="wp-header-end">

			<?php if ( $message ) : ?>
			<div id="message" class="<?php echo $class; ?> notice is-dismissible"><p><?php echo faq_get_message(); ?></p></div>
				<?php
				$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message', 'error' ), $_SERVER['REQUEST_URI'] );
			endif;
			?>

			<form class="search-form wp-clearfix" method="get">
				<input type="hidden" name="page" value="faq" />
				<?php $faq_headers->search_box( __( 'Search', 'page-utils' ), 'faq' ); ?>
			</form>

			<div id="col-container" class="wp-clearfix">

				<div id="col-left">
					<div class="col-wrap">
						<div class="form-wrap">
							<?php if( !$faq_id ) { ?>
							<h2><?php echo __('Add FAQ', 'page-utils'); ?></h2>
							<form id="addfaq" method="post">
								<input type="hidden" name="action" value="add-faq" />
								<?php wp_nonce_field( 'add-faq', '_wpnonce_add-faq' ); ?>
								<div class="form-field form-required term-name-wrap">
									<label for="faq-name"><?php _e( 'Name', 'page-utils' ); ?></label>
									<input name="faq-name" id="faq-name" type="text" value="" aria-required="true" />
									<p><?php _e( 'Enter your FAQ header name.' ); ?></p>
								</div>
								<?php submit_button( __('Add FAQ', 'page-utils') ); ?>
							</form>
							<?php } else { $faq_data = PU()->query->get_faq( $faq_id ); ?>
							<h2><?php echo __('Edit FAQ', 'page-utils'); ?></h2>
							<form id="editfaq" method="post">
								<input type="hidden" name="action" value="edit-faq" />
								<input type="hidden" name="faq-id" value="<?php echo $faq_id; ?>" />
								<?php wp_nonce_field( 'edit-faq', '_wpnonce_edit-faq' ); ?>
								<div class="form-field form-required term-name-wrap">
									<label for="faq-name"><?php _e( 'Name', 'page-utils' ); ?></label>
									<input name="faq-name" id="faq-name" type="text" value="<?php echo $faq_data->faq_name; ?>" aria-required="true" />
									<p><?php _e( 'Enter your FAQ header name.' ); ?></p>
								</div>
								<?php submit_button( __('Edit FAQ', 'page-utils') ); ?>
							</form>
							<?php } ?>
						</div>
					</div>
				</div><!-- /col-left -->

				<div id="col-right">
					<div class="col-wrap">
						<?php $faq_headers->views(); ?>

						<form id="faq-filter" method="post" action="">
							<?php wp_nonce_field( 'list-faq', '_wpnonce_list-faq' ); ?>
							<?php $faq_headers->display(); ?>
						</form>
					</div>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Output of backend QNA list page.
	 */
	public function faq_qna_list_handler(){
		global $wpdb;
		
    	$faq_question_answer = new FAQ_Admin_List_Table_QNA();
    	$faq_question_answer->prepare_items();
    	$message = ( isset( $_REQUEST['message'] ) && $_REQUEST['message'] != 0 ) ? absint( $_REQUEST['message'] ) : 0;
    	$referer  = wp_get_referer();
		if ( ! $referer ) { // For POST requests.
			$referer = wp_unslash( $_SERVER['REQUEST_URI'] );
		}
		$referer = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'error', 'message', 'paged' ), $referer );
		$faq_id = ( isset( $_REQUEST['faq_id'] ) ) ? absint( $_REQUEST['faq_id'] ) : 0;
		$qna_id = ( isset( $_REQUEST['qna_id'] ) ) ? absint( $_REQUEST['qna_id'] ) : 0;
		$class = ( isset( $_REQUEST['error'] ) ) ? 'error' : 'updated';
    	?>

		<div class="wrap nosubsub">
			<h1 class="wp-heading-inline"><?php echo __( 'FAQ Question & Answer', 'page-utils' ); ?> 
			<a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=faq');?>"><?php _e('Back to FAQ', 'page-utils')?></a>
		</h1>

			<?php
			if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
				/* translators: %s: search keywords */
				printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( wp_unslash( $_REQUEST['s'] ) ) );
			}
			?>

			<hr class="wp-header-end">

			<?php if ( $message ) : ?>
			<div id="message" class="<?php echo $class; ?> notice is-dismissible"><p><?php echo faq_get_message(); ?></p></div>
				<?php
				$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message', 'error' ), $_SERVER['REQUEST_URI'] );
			endif;
			?>

			<form class="search-form wp-clearfix" method="get">
				<input type="hidden" name="page" value="faq-qna" />
				<input type="hidden" name="faq_id" value="<?php echo $faq_id; ?>" />
				<?php $faq_question_answer->search_box( __( 'Search', 'page-utils' ), 'faq-qna' ); ?>
			</form>

			<div id="col-container" class="wp-clearfix">

				<div id="col-left">
					<div class="col-wrap">
						<div class="form-wrap">
							<?php if( !$qna_id ) { ?>
							<h2><?php echo __('Add Question & Answer', 'page-utils'); ?></h2>
							<form id="addfaqqna" method="post">
								<input type="hidden" name="faq-id" value="<?php echo $faq_id; ?>" />
								<input type="hidden" name="action" value="add-faq-qna" />
								<?php wp_nonce_field( 'add-faq-qna', '_wpnonce_add-faq-qna' ); ?>
								<div class="form-field form-required term-name-wrap">
									<label for="faq-question"><?php _e( 'Question', 'page-utils' ); ?></label>
									<input name="faq-question" id="faq-question" type="text" value="" aria-required="true" />
								</div>
								<div class="form-field form-required term-name-wrap">
									<label for="faq-answer"><?php _e( 'Answer', 'page-utils' ); ?></label>
									<textarea name="faq-answer" id="faq-answer" rows="5"></textarea>
								</div>
								<?php submit_button( __('Add Q&A', 'page-utils') ); ?>
							</form>
							<?php } else { $faq_qna_data = PU()->query->get_faq_qna( $qna_id ); ?>
							<h2><?php echo __('Edit Question & Answer', 'page-utils'); ?></h2>
							<form id="editfaqqna" method="post">
								<input type="hidden" name="qna-id" value="<?php echo $qna_id; ?>" />
								<input type="hidden" name="faq-id" value="<?php echo $faq_id; ?>" />
								<input type="hidden" name="action" value="edit-faq-qna" />
								<?php wp_nonce_field( 'edit-faq-qna', '_wpnonce_edit-faq-qna' ); ?>
								<div class="form-field form-required term-name-wrap">
									<label for="faq-question"><?php _e( 'Question', 'page-utils' ); ?></label>
									<input name="faq-question" id="faq-question" type="text" value="<?php echo $faq_qna_data->faq_question; ?>" aria-required="true" />
								</div>
								<div class="form-field form-required term-name-wrap">
									<label for="faq-answer"><?php _e( 'Answer', 'page-utils' ); ?></label>
									<textarea name="faq-answer" id="faq-answer" rows="5"><?php echo $faq_qna_data->faq_answer; ?></textarea>
								</div>
								<div class="form-field form-required term-name-wrap">
									<label for="faq-question"><?php _e( 'Order', 'page-utils' ); ?></label>
									<input name="faq-order" id="faq-order" type="number" min="0" value="<?php echo $faq_qna_data->faq_order; ?>" aria-required="true" />
								</div>
								<?php submit_button( __('Edit Q&A', 'page-utils') ); ?>
							</form>
							<?php } ?>
						</div>
					</div>
				</div><!-- /col-left -->

				<div id="col-right">
					<div class="col-wrap">
						<?php $faq_question_answer->views(); ?>

						<form id="faq-qna-filter" method="post" action="">
							<?php wp_nonce_field( 'list-faq-qna', '_wpnonce_list-faq-qna' ); ?>
							<?php $faq_question_answer->display(); ?>

						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Output of backend Href lang page.
	 */
	public function href_lang_handler() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo __( 'Hreflang Tags', 'page-utils' ); ?></h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'hreflang_tags_settings_option_group' );
                do_settings_sections( 'hreflang_tags-settings-admin' );
                submit_button();
            ?>
            </form>
            <?php do_action( 'after_hreflang_tags_settings_form' ); ?>
            <?php do_action( 'pu_settings_options_footer' )?>
		</div>
		<?php
	}

	/**
	 * Output of backend star rating page.
	 */
	public function star_rating_handler() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo __( 'Star Rating', 'page-utils' ); ?></h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'star_rating_settings_option_group' );
                do_settings_sections( 'star_rating-settings-admin' );
                submit_button();
            ?>
            </form>
            <?php do_action( 'after_star_rating_settings_form' ); ?>
            <?php do_action( 'pu_settings_options_footer' )?>
		</div>
		<?php
	}

	/**
	 * Output of backend star rating page.
	 */
	public function wp_svg_icons_handler() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo __( 'WP SVG Icons', 'page-utils' ); ?></h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'wp_svg_icons_settings_option_group' );
                do_settings_sections( 'wp_svg_icons-settings-admin' );
                submit_button();
            ?>
            </form>
            <?php do_action( 'after_wp_svg_icons_settings_form' ); ?>
            <?php do_action( 'pu_settings_options_footer' )?>
		</div>
		<?php
	}

	/**
	 * Add meta box for post types.
	 */
	public function add_hreflang_tag_meta_box() {
		$supported_content_types = hreflang_tags_get_settings('hreflang_tags_content_types');
		$supported_post_types = isset($supported_content_types['post_type']) ? $supported_content_types['post_type'] : array();
		foreach ( $supported_post_types as $type ) {
			add_meta_box('pu-hreflang-meta-box',__('Alternate Hreflang slug', 'page-utils'), array( $this, 'hreflang_tag_meta_box' ), $type, 'advanced', 'high', null);
		}
	}

	/**
	 * Output meta box for post types.
	 */
	public function hreflang_tag_meta_box() {
		global $post;
		$supported_lang_site_url = hreflang_tags_get_settings('supported_lang_site_url');
        $supported_lang = hreflang_tags_get_settings('supported_lang');
        $translations = pu_get_available_translations();
        $site_lang = get_option('WPLANG');
        $supported_post_slugs = get_post_meta( $post->ID, 'hreflang_tags_supported_post_slug', true );
        
        wp_nonce_field( "pu_hreflang_{$post->post_type}", "pu_hreflang_{$post->post_type}_nonce_field" );
        if( $supported_lang_site_url || $supported_lang ) { 
            for( $i = 0; $i < count( $supported_lang_site_url ); $i++) {
                if( !empty( $supported_lang_site_url[$i] ) || !empty( $supported_lang[$i] ) ) { 
                    $selected_site_url = ( isset($supported_lang_site_url[$i]) && !empty($supported_lang_site_url[$i]) ) ? $supported_lang_site_url[$i] : '';
                    $selected_lang = ( isset($supported_lang[$i]) && !empty($supported_lang[$i]) ) ? $supported_lang[$i] : '';
                ?>
                <p class="supported-base-langs-field_wrapper">
                	<label for="supported_lang_site_url">  
                		<strong>
                       	<?php 

                       	$translation = ($selected_lang && isset($translations[$selected_lang])) ? $translations[$selected_lang] : $translations[$site_lang];
                       	echo esc_html( $translation['english_name'] . ' - '. $translation['native_name'] ) . ": ";
                       	?>
                       	</strong>
                    </label>
                    <label for="supported_lang_site_url" class="alignright"> <?php echo trailingslashit($selected_site_url); ?> 
                        <input type="text" class="regular-text" name="hreflang_tags_supported_post_slug[<?php echo $selected_lang ?>]" placeholder="<?php _e('Add alternate slug', 'page-utils'); ?>" value="<?php echo isset($supported_post_slugs[$selected_lang]) ? $supported_post_slugs[$selected_lang] : ''; ?>" />
                    </label>
                </p>            
                <?php 
                }
            }
        }else{ ?>
        <p class="supported-base-langs-field_wrapper">
        	<label for="supported_lang_site_url">  
                <strong>
               	<?php 
               	$translation = $translations[$site_lang];
               	echo esc_html( $translation['english_name'] . ' - '. $translation['native_name'] ) . ": ";
               	?>
               	</strong>
            </label>
            <label for="supported_lang_site_url" class="alignright"> <?php echo trailingslashit($selected_site_url); ?> 
                <input type="text" class="regular-text" name="hreflang_tags_supported_post_slug[<?php echo $site_lang ?>]" placeholder="<?php _e('Add alternate slug', 'page-utils'); ?>" value="<?php echo isset($supported_post_slugs[$site_lang]) ? $supported_post_slugs[$site_lang] : ''; ?>" />
            </label>
        </p>
        <?php
        }
	}

	/**
	 * Save meta box for post types.
	 */
	public function save_hreflang_tag_meta_box() {
		global $post;
		if ( isset( $_POST["pu_hreflang_{$post->post_type}_nonce_field"] ) && wp_verify_nonce( $_POST["pu_hreflang_{$post->post_type}_nonce_field"], "pu_hreflang_{$post->post_type}" ) ) { 
		   	if( isset($_POST['hreflang_tags_supported_post_slug']) ){
		   		update_post_meta( $post->ID, 'hreflang_tags_supported_post_slug', $_POST['hreflang_tags_supported_post_slug'] );
		   	}
		}
	}

	/**
	 * Output add term meta box for taxonomy.
	 */
	public function add_hreflang_tag_term_meta_box() {

		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? sanitize_key( $_REQUEST['taxonomy'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$post_type = isset( $_REQUEST['post_type'] ) ? sanitize_key( $_REQUEST['post_type'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$post_type = isset( $GLOBALS['post_type'] ) ? $GLOBALS['post_type'] : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) || ! post_type_exists( $post_type ) ) {
			return;
		}

		$supported_lang_site_url = hreflang_tags_get_settings('supported_lang_site_url');
        $supported_lang = hreflang_tags_get_settings('supported_lang');
        $translations = pu_get_available_translations();
        $site_lang = get_option('WPLANG');
        ?>
        <p><label style="color: #23282d;"><strong><?php _e('Alternate Hreflang slug', 'page-utils'); ?></strong></label></p>
        <?php
        wp_nonce_field( "pu_hreflang_{$taxonomy}", "pu_hreflang_{$taxonomy}_nonce_field" );
        if( $supported_lang_site_url || $supported_lang ) { 
            for( $i = 0; $i < count( $supported_lang_site_url ); $i++) {
                if( !empty( $supported_lang_site_url[$i] ) || !empty( $supported_lang[$i] ) ) { 
                    $selected_site_url = ( isset($supported_lang_site_url[$i]) && !empty($supported_lang_site_url[$i]) ) ? $supported_lang_site_url[$i] : '';
                    $selected_lang = ( isset($supported_lang[$i]) && !empty($supported_lang[$i]) ) ? $supported_lang[$i] : '';
                ?>
                <p class="supported-base-langs-field_wrapper">
                	<label for="supported_lang_site_url">  
                		<strong>
                       	<?php 
                       	$translation = ($selected_lang && isset($translations[$selected_lang])) ? $translations[$selected_lang] : $translations[$site_lang];
                       	echo esc_html( $translation['english_name'] . ' - '. $translation['native_name'] ) . ": ";
                       	?>
                       	</strong>
                    </label>
                    <label for="supported_lang_site_url" class="alignright"> <?php echo trailingslashit($selected_site_url); ?> 
                        <input type="text" class="regular-text" name="hreflang_tags_supported_taxonomy_slug[<?php echo $selected_lang ?>]" placeholder="<?php _e('Add alternate slug', 'page-utils'); ?>" value="" />
                    </label>
                </p>            
                <?php 
                }
            }
        }else{ ?>
        <p class="supported-base-langs-field_wrapper">
        	<label for="supported_lang_site_url">  
        		<strong>
               	<?php 
               	$translation = $translations[$site_lang];
               	echo esc_html( $translation['english_name'] . ' - '. $translation['native_name'] ) . ": ";
               	?>
               </strong>
            </label>
            <label for="supported_lang_site_url" class="alignright"> <?php echo trailingslashit($selected_site_url); ?> 
                <input type="text" class="regular-text" name="hreflang_tags_supported_taxonomy_slug[<?php echo $site_lang ?>]" placeholder="<?php _e('Add alternate slug', 'page-utils'); ?>" value="" />
            </label>
        </p>
        <?php
        }
	}

	/**
	 * Output edit term meta box for taxonomy.
	 */
	public function edit_hreflang_tag_term_meta_box( $term ) {

		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? sanitize_key( $_REQUEST['taxonomy'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$post_type = isset( $_REQUEST['post_type'] ) ? sanitize_key( $_REQUEST['post_type'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$post_type = isset( $GLOBALS['post_type'] ) ? $GLOBALS['post_type'] : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( ! post_type_exists( $post_type ) ) {
			return;
		}

		$term_id = $term->term_id;
		$taxonomy = $term->taxonomy;

		$supported_lang_site_url = hreflang_tags_get_settings('supported_lang_site_url');
        $supported_lang = hreflang_tags_get_settings('supported_lang');
        $translations = pu_get_available_translations();
        $site_lang = get_option('WPLANG');
        $supported_term_slugs = get_term_meta( $term_id, 'hreflang_tags_supported_taxonomy_slug', true );
        ?>
        <p><label style="color: #23282d;"><strong><?php _e('Alternate Hreflang slug', 'page-utils'); ?></strong></label></p>
        <?php
        wp_nonce_field( "pu_hreflang_{$taxonomy}", "pu_hreflang_{$taxonomy}_nonce_field" );
        if( $supported_lang_site_url || $supported_lang ) { 
            for( $i = 0; $i < count( $supported_lang_site_url ); $i++) {
                if( !empty( $supported_lang_site_url[$i] ) || !empty( $supported_lang[$i] ) ) { 
                    $selected_site_url = ( isset($supported_lang_site_url[$i]) && !empty($supported_lang_site_url[$i]) ) ? $supported_lang_site_url[$i] : '';
                    $selected_lang = ( isset($supported_lang[$i]) && !empty($supported_lang[$i]) ) ? $supported_lang[$i] : '';
                ?>
                <p class="supported-base-langs-field_wrapper">
                	<label for="supported_lang_site_url">  
                		<strong>
                       	<?php 
                       	$translation = ($selected_lang && isset($translations[$selected_lang])) ? $translations[$selected_lang] : $translations[$site_lang];
                       	echo esc_html( $translation['english_name'] . ' - '. $translation['native_name'] ) . ": ";
                       	?>
                       	</strong>
                    </label>
                    <label for="supported_lang_site_url" class="alignright"> <?php echo trailingslashit($selected_site_url); ?> 
                        <input type="text" class="regular-text" name="hreflang_tags_supported_taxonomy_slug[<?php echo $selected_lang ?>]" placeholder="<?php _e('Add alternate slug', 'page-utils'); ?>" value="<?php echo isset($supported_term_slugs[$selected_lang]) ? $supported_term_slugs[$selected_lang] : ''; ?>" />
                    </label>
                </p>            
                <?php 
                }
            }
        }else{ ?>
        <p class="supported-base-langs-field_wrapper">
        	<label for="supported_lang_site_url">  
        		<strong>
               	<?php 
               	$translation = $translations[$site_lang];
               	echo esc_html( $translation['english_name'] . ' - '. $translation['native_name'] ) . ": ";
               	?>
               </strong>
            </label>
            <label for="supported_lang_site_url" class="alignright"> <?php echo trailingslashit($selected_site_url); ?> 
                <input type="text" class="regular-text" name="hreflang_tags_supported_taxonomy_slug[<?php echo $site_lang ?>]" placeholder="<?php _e('Add alternate slug', 'page-utils'); ?>" value="<?php echo isset($supported_term_slugs[$site_lang]) ? $supported_term_slugs[$site_lang] : ''; ?>" />
            </label>
        </p>
        <?php
        }
	}

	/**
	 * Save term meta box for taxonomy.
	 */
	public function save_hreflang_tag_term_meta_box( $term_id, $tt_id, $taxonomy ) {
		global $post;
		if ( ! empty( $_POST ) && isset( $_POST["pu_hreflang_{$taxonomy}_nonce_field"] ) && wp_verify_nonce( $_POST["pu_hreflang_{$taxonomy}_nonce_field"], "pu_hreflang_{$taxonomy}" ) ) {
		   	if( isset($_POST['hreflang_tags_supported_taxonomy_slug']) ){
		   		update_term_meta( $term_id, 'hreflang_tags_supported_taxonomy_slug', $_POST['hreflang_tags_supported_taxonomy_slug'] );
		   	}
		}
	}

	/**
	 * Enqueue scripts & styles.
	 */
	public function admin_enqueue_scripts() {

		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		
		//if ( in_array( $screen_id, array( 'page-utils_page_faq', 'page-utils_page_faq-qna', 'page-utils_page_href_lang' ) ) ) {
		wp_enqueue_style( 'wp-color-picker' );
    	wp_enqueue_script( 'wp-color-picker');
		wp_enqueue_script( 'page_utils_js', PU()->plugin_url() . '/assets/js/page_utils.js', array( 'jquery', 'wp-color-picker' ), PU_VERSION, true );
		//}

	}

	/**
	 * Add styles in admin head.
	 */
	public function admin_head_styles() {
		?>
		<style>
			#toplevel_page_page-utils .wp-submenu li a[href="admin.php?page=faq-qna"] { display: none; }
		</style>
		<?php
	}

	/**
	 * Output of backend FAQ Settings page.
	 */
	public function pu_settings_handler() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Global Settings', 'page-utils' ); ?></h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'pu_settings_option_group' );
                do_settings_sections( 'pu-settings-admin' );
                submit_button();
            ?>
            </form>
            <?php do_action( 'pu_settings_options_footer' )?>
        </div>
        <?php
	}

	public function pu_settings_options_footer() {
		?>
		<p class="faq-settings-footer alignright"><?php echo __('Page Utils version', 'page-utils'). ' '. PU()->version; ?></p>
		<?php
	}

	/**
	 * Output of backend GDPR cookie page.
	 */
	public function gdpr_cookie_handler() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo __( 'GDPR Cookie', 'page-utils' ); ?></h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'gdpr_cookie_settings_option_group' );
                do_settings_sections( 'gdpr_cookie-settings-admin' );
                submit_button();
            ?>
            </form>
            <?php do_action( 'after_gdpr_cookie_settings_form' ); ?>
            <?php do_action( 'pu_settings_options_footer' )?>
		</div>
		<?php
	}

	/**
	 * Output of backend PU Updater page.
	 */
	public function pu_updater_handler() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo __( 'Page Utils Updater', 'page-utils' ); ?></h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'pu_updater_settings_option_group' );
                do_settings_sections( 'pu_updater-settings-admin' );
                submit_button();
            ?>
            </form>
            <?php do_action( 'after_pu_updater_settings_form' ); ?>
            <?php do_action( 'pu_settings_options_footer' )?>
		</div>
		<?php
	}
}

return new PU_Admin();