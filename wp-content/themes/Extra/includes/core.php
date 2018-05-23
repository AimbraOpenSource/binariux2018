<?php
// Prevent file from being loaded directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Extra Theme
 *
 * includes/core.php
 *
 * Main theme setup
 */

function et_setup_theme() {
	global $themename, $shortname, $et_store_options_in_one_row;
	$themename = 'Extra';
	$shortname = 'extra';
	$et_store_options_in_one_row = true;

	$template_directory = get_template_directory();

	$theme_version = et_get_theme_version();

	define( 'ET_CORE_VERSION', $theme_version );

	// ePanel
	require_once $template_directory . '/epanel/custom_functions.php';
	require_once $template_directory . '/epanel/core_functions.php';
	require_once $template_directory . '/post_thumbnails_extra.php';
	require_once $template_directory . '/includes/choices.php';
	require_once $template_directory . '/includes/sanitization.php';

	// Core
	require_once $template_directory . '/core/init.php';

	et_core_setup( get_template_directory_uri() );

	load_theme_textdomain( 'extra', $template_directory . '/lang' );

	// deactivate page templates and custom import functions
	remove_action( 'init', 'et_activate_features' );

	// remove epanel theme options link in wp-admin menu
	remove_action( 'admin_menu', 'et_add_epanel' );
	// end ePanel

	register_nav_menus( array(
		'primary-menu'   => esc_html__( 'Primary Menu', 'extra' ),
		'secondary-menu' => esc_html__( 'Secondary Menu', 'extra' ),
		'footer-menu'    => esc_html__( 'Footer Menu', 'extra' ),
	) );

	add_theme_support( 'title-tag' );

	add_theme_support( 'et-post-formats', array(
		'video',
		'audio',
		'quote',
		'gallery',
		'link',
		'map',
		'text',
	) );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'et_widget_areas' );

	add_theme_support( 'html5', array( 'search-form' ) );
}

add_action( 'after_setup_theme', 'et_setup_theme' );

function extra_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'extra_content_width', 1080 );
}

add_action( 'after_setup_theme', 'extra_content_width', 0 );

function extra_add_viewport_meta(){
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1" />';
}

add_action( 'wp_head', 'extra_add_viewport_meta' );

function extra_custom_background_args() {
	return array(
		'wp-head-callback' => 'extra_custom_background_cb',
		'default-color'    => '#eceff4',
	);
}

add_filter( 'et_custom_background_args', 'extra_custom_background_args' );

function extra_custom_background_cb() {
	// $background is the saved custom image, or the default image.
	$background = set_url_scheme( get_background_image() );

	// $color is the saved custom color.
	// A default has to be specified in style.css. It will not be printed here.
	$color = get_background_color();

	if ( $color === get_theme_support( 'custom-background', 'default-color' ) ) {
		$color = false;
	}

	if ( ! $background && ! $color ) {
		return;
	}

	$style = $color ? "background-color: #$color;" : '';

	if ( $background ) {
		$image = " background-image: url('$background');";

		$_repeat = get_theme_mod( 'background_repeat', get_theme_support( 'custom-background', 'default-repeat' ) );
		if ( ! in_array( $_repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) ) {
			$_repeat = 'repeat';
		}

		$repeat = " background-repeat: $_repeat;";

		if ( 'no-repeat' == $_repeat ) {
			$repeat .= " background-size: cover;";
		}

		$position = get_theme_mod( 'background_position_x', get_theme_support( 'custom-background', 'default-position-x' ) );

		if ( ! in_array( $position, array( 'center', 'right', 'left' ) ) ) {
			$position = 'left';
		}

		$position = " background-position: top $position;";

		$attachment = get_theme_mod( 'background_attachment', get_theme_support( 'custom-background', 'default-attachment' ) );

		if ( ! in_array( $attachment, array( 'fixed', 'scroll' ) ) ) {
			$attachment = 'scroll';
		}

		$attachment = " background-attachment: $attachment;";

		$style .= $image . $repeat . $position . $attachment;
	}
?>
<style type="text/css" id="extra-custom-background-css">
body.custom-background { <?php echo trim( $style ); ?> }
</style>
<?php
}

function extra_add_image_sizes() {
	$sizes = array(
		'extra-image-huge'          => array(
			'width'  => 1280,
			'height' => 768,
			'crop'   => true,
		),
		'extra-image-single-post'   => array(
			'width'  => 1280,
			'height' => 640,
			'crop'   => true,
		),
		'extra-image-medium'        => array(
			'width'  => 627,
			'height' => 376,
			'crop'   => true,
		),
		'extra-image-small'         => array(
			'width'  => 440,
			'height' => 264,
			'crop'   => true,
		),
		'extra-image-square-medium' => array(
			'width'  => 440,
			'height' => 440,
			'crop'   => true,
		),
		'extra-image-square-small'  => array(
			'width'  => 150,
			'height' => 150,
			'crop'   => true,
		),
	);

	foreach ( $sizes as $name => $size_info ) {
		add_image_size( $name, $size_info['width'], $size_info['height'], $size_info['crop'] );
	}
}

add_action( 'after_setup_theme', 'extra_add_image_sizes' );

function extra_register_post_types() {
	$labels = array(
		'name'               => esc_html_x( 'Category Layouts', 'layout type general name', 'extra' ),
		'singular_name'      => esc_html_x( 'Category Layout', 'layout type singular name', 'extra' ),
		'add_new'            => esc_html_x( 'Add New', 'layout item', 'extra' ),
		'add_new_item'       => esc_html__( 'Add New Category Layout', 'extra' ),
		'edit_item'          => esc_html__( 'Edit Category Layout', 'extra' ),
		'new_item'           => esc_html__( 'New Category Layout', 'extra' ),
		'all_items'          => esc_html__( 'All Category Layouts', 'extra' ),
		'view_item'          => esc_html__( 'View Category Layout', 'extra' ),
		'search_items'       => esc_html__( 'Search Layouts', 'extra' ),
		'not_found'          => esc_html__( 'Nothing found', 'extra' ),
		'not_found_in_trash' => esc_html__( 'Nothing found in Trash', 'extra' ),
		'parent_item_colon'  => '',
	);

	$args = array(
		'labels'          => $labels,
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => false,
		'can_export'      => true,
		'query_var'       => false,
		'has_archive'     => false,
		'capability_type' => 'post',
		'hierarchical'    => false,
		'menu_position'   => null,
		'supports'        => array(
			'title',
			'editor',
			'revisions',
		),
	);

	register_post_type( EXTRA_LAYOUT_POST_TYPE, apply_filters( 'extra_layout_post_type_args', $args ) );
	register_taxonomy_for_object_type( 'category', 'layout' );
}

add_action( 'init', 'extra_register_post_types', 0 );

if ( ! function_exists( 'et_extra_fonts_url' ) ) :

	function et_extra_fonts_url() {
		$fonts_url = '';

		/* Translators: If there are characters in your language that are not
		 * supported by Open Sans, translate this to 'off'. Do not translate
		 * into your own language.
		 */
		$open_sans = esc_html_x( 'on', 'Open Sans font: on or off', 'extra' );

		if ( 'off' !== $open_sans ) {
			$font_families = array();

			if ( 'off' !== $open_sans ) {
				$font_families[] = 'Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800';
			}

			$protocol   = is_ssl() ? 'https' : 'http';

			$query_args = array(
				'family' => implode( '%7C', $font_families ),
				'subset' => 'latin,latin-ext',
			);

			$fonts_url = add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" );
		}

		return $fonts_url;
	}

endif;

/**
 * Load default fonts
 * @return void
 */
function et_extra_load_fonts() {
	$fonts_url        = et_extra_fonts_url();
	$custom_body_font = et_get_option( 'body_font', false );

	// Load Open Sans if it is valid for the language setting and no custom body_font defined
	if ( ! empty( $fonts_url ) && ! $custom_body_font ) {

		// Deregister WordPress' Open Sans queue which is used for admin bar to prevent duplication & confusion
		wp_deregister_style( 'open-sans' );
		wp_register_style( 'open-sans', false );

		// Enqueue Open Sans
		wp_enqueue_style( 'extra-fonts', esc_url_raw( $fonts_url ), array(), null );
	}
}

add_action( 'wp_enqueue_scripts', 'et_extra_load_fonts' );

function extra_load_scripts_styles(){
	$theme_version = SCRIPT_DEBUG ? time() : et_get_theme_version();
	$template_dir = get_template_directory_uri();
	$suffix = SCRIPT_DEBUG ? '.js' : '.min.js';
	$extra_scripts_dependencies = apply_filters( 'extra_scripts_dependencies', array( 'jquery', 'imagesloaded' ) );

	// Load dependencies conditionally
	if ( is_page_template( 'page-template-authors.php' ) || 'Masonry' === et_get_option( 'archive_list_style', 'Standard' ) || is_page_template( 'page-template-blog-feed.php' ) ) {
		$extra_scripts_dependencies[] = 'salvattore';
	}

	/*
	 * Loads the main stylesheet.
	 */
	wp_enqueue_style( 'extra-style', get_stylesheet_uri(), array(), $theme_version );

	wp_register_script( 'validation', $template_dir . '/scripts/ext/jquery.validate' . $suffix, array( 'jquery' ), $theme_version, true );
	wp_register_script( 'raty', $template_dir . '/scripts/ext/jquery.raty' . $suffix, array( 'jquery' ), $theme_version, true );

	wp_enqueue_script( 'imagesloaded', $template_dir . '/scripts/ext/imagesloaded' . $suffix, array( 'jquery' ), $theme_version, true );// todo, load only when needed?

	wp_enqueue_script( 'waypoints', $template_dir . '/scripts/ext/jquery.waypoints' . $suffix, array( 'jquery' ), $theme_version, true );// todo, load only when needed?
	wp_enqueue_script( 'fitvids', $template_dir . '/scripts/ext/jquery.fitvids' . $suffix, array( 'jquery' ), $theme_version, true );// todo, load only when needed?

	if ( 'on' === et_get_option( 'extra_smooth_scroll', false ) ) {
		wp_enqueue_script( 'smooth-scroll', $template_dir . '/scripts/ext/smoothscroll' . $suffix, array( 'jquery' ), $theme_version, true );
	}

	wp_enqueue_script( 'masonry' );// todo, load only when needed?
	wp_enqueue_script( 'extra-scripts', $template_dir . '/scripts/scripts' . $suffix, $extra_scripts_dependencies, $theme_version, true );
	wp_localize_script( 'extra-scripts', 'EXTRA', array(
		'images_uri'                   => $template_dir . '/images/',
		'ajaxurl'                      => set_url_scheme( admin_url( 'admin-ajax.php' ) ),
		'your_rating'                  => esc_html__( 'Sua classificação:', 'extra' ),
		'rating_nonce'                 => wp_create_nonce( 'extra_rating_nonce' ),
		'timeline_nonce'               => wp_create_nonce( 'timeline_nonce' ),
		'blog_feed_nonce'              => wp_create_nonce( 'blog_feed_nonce' ),
		'error'                        => esc_html__( 'Ocorreu um problema, tente novamente.', 'extra' ),
		'contact_error_name_required'  => esc_html__( 'O campo nome não pode estar em branco.', 'extra' ),
		'contact_error_email_required' => esc_html__( 'O campo email não pode estar vazio.', 'extra' ),
		'contact_error_email_invalid'  => esc_html__( 'Por favor, digite um e-mail válido.', 'extra' ),
		'is_ab_testing_active'         => et_is_ab_testing_active(),
		'is_cache_plugin_active'       => false === et_pb_detect_cache_plugins() ? 'no' : 'yes',
	) );

	if ( is_singular() ) {

		if ( is_singular( 'post' ) ) {

			if ( et_has_post_format( 'map' ) ) {
				et_extra_enqueue_google_maps_api();
			}

			wp_enqueue_script( 'raty' );

			if ( comments_open() && get_option( 'thread_comments' ) ) {
				wp_enqueue_script( 'comment-reply' );
			}
		}

		if ( is_page() ) {
			if ( is_page_template( 'page-template-timeline.php' ) ) {
				wp_enqueue_script( 'hash-persistance' );
			}

			if ( is_page_template( 'page-template-contact.php' ) ) {
				wp_enqueue_script( 'validation' );
				wp_enqueue_script( 'jquery-effects-highlight' );
				et_extra_enqueue_google_maps_api();
			}
		}
	}
}

add_action( 'wp_enqueue_scripts', 'extra_load_scripts_styles' );

function extra_dynamic_styles( $option = "" ) {
	$selectors = extra_get_customizer_dynamic_selectors_settings();

	$selectors = apply_filters( 'extra_dynamic_styles', $selectors );

	return !empty( $option ) && !empty( $selectors[$option] ) ? $selectors[$option] : $selectors;
}

function extra_print_dynamic_styles() {
	$shared_paramless_callbacks = array();

	foreach ( extra_dynamic_styles() as $option_name => $option_properties ) {

		$option_properties['default'] = isset( $option_properties['default'] ) ? $option_properties['default'] : '';

		$value = esc_html( et_get_option( $option_name, $option_properties['default'] ) );

		$option_properties = apply_filters( 'extra_print_dynamic_styles-' . $option_name, $option_properties, $value );

		$default = ! empty( $option_properties['default'] ) ? (string) $option_properties['default'] : "";

		if ( strtolower( $default ) === strtolower( $value ) || ( "" === strtolower( $value ) && "0" === strtolower( $default ) ) || ! $option_properties ) {
			continue;
		}

		$style_id = 'extra-dynamic-styles-' . esc_attr( $option_name );

		$output = "\n".'<style id="' . $style_id . '" type="text/css">' . "\n";

		$value_bind = $option_properties['value_bind'];
		$value_bind_style = $value_bind['style'];
		$value_bind_property_selectors = $value_bind['property_selectors'];

		if ( 'dynamic_selectors' === $value_bind_style ) {
			foreach ( $value_bind_property_selectors as $property => $property_selectors ) {

				if ( empty( $property_selectors ) ) {
					continue;
				}

				$property_selectors = implode( ",\n", $property_selectors );
				$prop_style_id = $style_id . '-' . $property;

				$css = extra_dynamic_selector_css( $property_selectors, $value, $property );

				$css = apply_filters( 'extra_print_dynamic_styles-' . $prop_style_id . '-css_output', $css, $option_properties, $value );
				echo extra_set_dynamic_style_el( $prop_style_id, $css );
			}
		} else if ( 'dynamic_selectors_value_format' === $value_bind_style ) {
			foreach ( $value_bind_property_selectors as $property_options ) {

				if ( empty( $property_options['selectors'] ) ) {
					continue;
				}

				$property_selectors = implode( ",\n", $property_options['selectors'] );
				$format = $property_options['format'];

				if ( empty( $value ) ) {
					continue 2;
				}

				$formatted_value = str_replace( '%value%', $value, $format );
				$property = $property_options['property'];
				$prop_style_id = $style_id . '-' . $property;

				if ( !empty( $value_bind['use_only_formatted_value'] ) ) {
					$css = extra_dynamic_selector_css( $property_selectors, $formatted_value );
				} else {
					$css = extra_dynamic_selector_css( $property_selectors, $formatted_value, $property );
				}

				$css = apply_filters( 'extra_print_dynamic_styles-' . $prop_style_id . '-css_output', $css, $option_properties, $value );
				echo extra_set_dynamic_style_el( $prop_style_id, $css );
			}
		} else if ( 'dynamic_selectors_value_format_callback' === $value_bind_style ) {
			foreach ( $value_bind_property_selectors as $property => $property_selectors ) {

				$use_formatted_value_as_css_expression = (bool) !empty( $value_bind['use_formatted_value_as_css_expression'] );

				if ( empty( $property_selectors ) && ! $use_formatted_value_as_css_expression ) {
					continue;
				}

				if ( ! $use_formatted_value_as_css_expression ) {
					$property_selectors = implode( ",\n", $property_selectors );
				}

				$callback = $value_bind['value_format_callback'];
				$prop_style_id = $style_id . '-' . $property;

				if ( is_callable( $callback ) ) {
					$formatted_value = $callback( $option_name, $property, $value );
				} else {
					_doing_it_wrong( __FUNCTION__, sprintf( et_get_safe_localization( __( 'Callback function: "%s" doesnt exist', 'extra' ) ), $callback ), et_get_theme_version() );
					continue;
				}

				if ( $use_formatted_value_as_css_expression ) {
					$css = $formatted_value;
				} else if ( !empty( $value_bind['use_only_formatted_value'] ) ) {
					$css = extra_dynamic_selector_css( $property_selectors, $formatted_value );
				} else {
					$css = extra_dynamic_selector_css( $property_selectors, $formatted_value, $property );
				}

				$css = apply_filters( 'extra_print_dynamic_styles-' . $prop_style_id . '-css_output', $css, $option_properties, $value );
				echo extra_set_dynamic_style_el( $prop_style_id, $css );
			}
		} else if ( 'dynamic_selectors_shared_paramless_callback' === $value_bind_style ) {
			// Paramless callbacks should be printed once only
			$shared_paramless_callbacks[$value_bind['value_format_callback']] = $value_bind['value_format_callback'];
		}
	}

	foreach ( $shared_paramless_callbacks as $shared_paramless_callback ) {

		if ( is_callable( $shared_paramless_callback ) ) {
			// The parameters on param callback is kept as is to mimic its dynamic_selectors_value_format_callback counterpart
			// and provide support for passing all customizer options via AJAX-based callback for option which has no JS callback
			$css = $shared_paramless_callback( null, null, null );
		} else {
			_doing_it_wrong( __FUNCTION__, sprintf( et_get_safe_localization( __( 'Callback function: "%s" doesnt exist', 'extra' ) ), $shared_paramless_callback ), et_get_theme_version() );
			continue;
		}

		$css = apply_filters( 'extra_print_dynamic_styles-' . $shared_paramless_callback . '-css_output', $css );
		echo extra_set_dynamic_style_el( 'extra-dynamic-styles-' . $shared_paramless_callback, $css );
	}
}

add_action( 'wp_head', 'extra_print_dynamic_styles' );
add_action( 'customize_controls_print_styles', 'extra_print_dynamic_styles' );

function extra_set_dynamic_style_el( $style_id, $css ) {
	if ( !empty( $css ) ) {
		return "\n" . '<style type="text/css" id="' . esc_attr( $style_id ) . '">' . "\n" . $css  .'</style>' . "\n";
	}
}

function extra_dynamic_selector_css( $property_selectors, $value, $css_property = '' ) {
	if ( empty( $value ) ) {
		return;
	}

	if ( !empty( $css_property ) ) {
		$css_expression = $css_property . ': ' . $value . ';';
	} else {
		$css_expression = $value;
	}

	if ( !empty( $css_expression ) ) {
		return $property_selectors . ' {' . "\n\t" . $css_expression . "\n" . '}' . "\n\n";
	}
}

function extra_classes( $classes = array(), $selector = '', $return_array = true ) {
	$_classes = array();
	if ( ! empty( $classes ) ) {
		foreach ( $classes as $class ) {
			if ( is_array( $class ) ) {
				foreach ( $class as $class_array_item ) {
					$_classes[] = $class_array_item;
				}
			} else {
				if ( strpos( $class, ' ' ) !== false ) {
					foreach ( explode( ' ', $class ) as $class_array_item ) {
						$_classes[] = $class_array_item;
					}
				} else {
					$_classes[] = $class;
				}
			}
		}
	}

	$classes = array_map( 'trim', $_classes );

	if ( !empty( $selector ) && is_string( $selector ) ) {
		$classes = apply_filters( 'extra_classes_' . esc_attr( $selector ), $classes );
	}

	if ( empty( $classes ) ) {
		return;
	}

	$classes = array_unique( $classes );
	$classes = array_map( 'trim', $classes );
	$classes = array_map( 'esc_attr', $classes );

	$_classes = array();
	foreach ( $classes as $class ) {
		if ( ! empty( $class ) ) {
			$_classes[] = $class;
		}
	}

	return $return_array ? $_classes : implode( ' ', $_classes );
}

function extra_get_dynamic_selectors() {
	$dynamic_selectors = array(
		'main_column_with_sidebar'      => '.with_sidebar .et_pb_extra_column_main',
		'sidebar_column'                => '.with_sidebar .et_pb_extra_column_sidebar',
		'logo'                          => '#logo',
		'header_search_field'           => '#top-header .et-top-search',
		'header_social_icons'           => '#top-header ul.et-extra-social-icons',
		'header_trending_bar'           => '#top-header .et-trending',
		'header_cart_total'             => '#top-header .et-top-cart-total',
		'main_navigation'               => '#et-navigation',
		'top_navigation'                => '#et-secondary-nav',
		'footer_social_icons'           => '#footer-nav ul.et-extra-social-icons',
		'footer_social_icons_icon'      => '#footer #footer-bottom #footer-nav ul.et-extra-social-icons .et-extra-icon',
		'footer_body_and_links'         => array(
			'#footer',
			'#footer li',
			'#footer p',
			'#footer a',
			'#footer span',
			'#footer .post-meta',
		),
		'footer_widget_body'            => array(
			'#footer .et_pb_widget',
			'#footer div',
			'#footer .et_pb_widget p',
			'#footer .et_pb_widget ins',
			'#footer .et_pb_widget span',
			'#footer .et_pb_widget strong',
			'#footer .widget_list li .post-meta',
			'#footer .et_pb_widget .recentcomments .post-title',
			'#footer .et_pb_widget .recentcomments .comment-author-link',
			'#footer .et_pb_widget .recentcomments .author',
			'#footer .widget_calendar td',
			'#footer .widget_et_recent_tweets .et-extra-icon:before',
		),
		'footer_widget_links'           => array(
			'#footer div',
			'#footer .et_pb_widget',
			'#footer .et_pb_widget a',
			'#footer .et_pb_widget a:visited',
			'#footer .et_pb_widget a span',
			'#footer .et_pb_widget ul li a',
			'#footer .et_pb_widget .widget-list li a',
			'#footer .et_pb_widget #recentcomments li a',
			'#footer .et_pb_widget .title',
			'#footer .et_pb_widget p',
			'#footer .et_pb_widget ins',
			'#footer .et_pb_widget span',
			'#footer .et_pb_widget strong',
			'#footer .widget_list li .post-meta',
			'#footer .et_pb_widget .recentcomments .post-title',
			'#footer .et_pb_widget .recentcomments .comment-author-link',
			'#footer .et_pb_widget .recentcomments .author',
			'#footer .widget_calendar td',
			'#footer .widget_et_recent_tweets .et-extra-icon:before',
		),
		'footer_heading'                => array(
			'#footer h4',
			'#footer .et_pb_widget h4.widgettitle',
		),
		'body_heading'                  => array(
			'h1',
			'h2',
			'h3',
			'h4',
			'h5',
			'h6',
			'h1 a',
			'h2 a',
			'h3 a',
			'h4 a',
			'h5 a',
			'h6 a',
		),
		'archive_heading'               => array(
			'.archive h1',
			'.search h1',
		),
		'buttons'                       => array(
			'#page-container .button',
			'#page-container button',
			'#page-container button[type="submit"]',
			'#page-container input[type="submit"]',
			'#page-container input[type="reset"]',
			'#page-container input[type="button"]',
			'.read-more-button',
			'.comment-body .comment_area .comment-content .reply-container .comment-reply-link',
			'.widget_tag_cloud a',
			'.widget_tag_cloud a:visited',
			'.post-nav .nav-links .button',
			'a.read-more-button',
			'a.read-more-button:visited',
			'#footer .widget_tag_cloud a',
			'#footer .widget_tag_cloud a:visited',
			'#footer a.read-more-button',
			'#footer a.read-more-button:visited',
			'#footer .button',
			'#footer button',
			'#footer button[type="submit"]',
			'#footer input[type="submit"]',
			'#footer input[type="reset"]',
			'#footer input[type="button"]',
		),
		'accent_color_color'            => array(
			'.widget_et_recent_tweets .widget_list a',
			'.widget_et_recent_tweets .et-extra-icon',
			'.widget_et_recent_tweets .widget-footer .et-extra-social-icon',
			'.widget_et_recent_tweets .widget-footer .et-extra-social-icon::before',
			'.project-details .project-details-title',
			'.et_filterable_portfolio .filterable_portfolio_filter a.current',
			'.et_extra_layout .et_pb_column .module-head h1',
			'.et_pb_extra_column .module-head h1',
			'#portfolio_filter a.current',
			'.woocommerce div.product div.summary .product_meta a',
			'.woocommerce-page div.product div.summary .product_meta a',
			'.et_pb_widget.woocommerce .product_list_widget li .amount',
			'.et_pb_widget li a:hover, .et_pb_widget.woocommerce .product_list_widget li a:hover',
			'.et_pb_widget.widget_et_recent_videos .widget_list .title:hover',
			'.et_pb_widget.widget_et_recent_videos .widget_list .title.active',
			'.woocommerce .woocommerce-info:before',
		),
		'accent_color_background_color' => array(
			'.single .score-bar',
			'.widget_et_recent_reviews .review-breakdowns .score-bar',
			'.et_pb_extra_module .posts-list article .post-thumbnail',
			'.et_extra_other_module .posts-list article .post-thumbnail',
			'.et_pb_widget .widget_list_portrait',
			'.et_pb_widget .widget_list_thumbnail',
			'.quote-format', /* Check */
			'.link-format', /* Check */
			'.audio-format .audio-wrapper',
			'.paginated .pagination li.active',
			'.score-bar', /* ../styles/modules/single/post.less */
			'.review-summary-score-box', /* ../styles/modules/single/post.less */
			'.post-footer .rating-stars #rating-stars img.star-on', /* ../styles/modules/single/post.less */
			'.post-footer .rating-stars #rated-stars img.star-on', /* ../styles/modules/single/post.less */
			'.author-box-module .author-box-avatar', /* ../styles/modules/single/post.less */
			'.timeline-menu li.active a:before',
			'.woocommerce div.product form.cart .button',
			'.woocommerce-page div.product form.cart .button',
			'.woocommerce div.product form.cart .read-more-button',
			'.woocommerce-page div.product form.cart .read-more-button',
			'.woocommerce div.product form.cart .post-nav .nav-links .button',
			'.woocommerce-page div.product form.cart .post-nav .nav-links .button',
			'.woocommerce .woocommerce-message, .woocommerce-page .woocommerce-message',
		),
		'accent_color_border_color'     => array(
			'#et-menu > li > ul',
			'#et-menu li > ul',
			'#et-menu > li > ul > li > ul',
			'.et-top-search-primary-menu-item .et-top-search',
			'.et_pb_module',
			'.module',
			'.page article',
			'.authors-page .page',
			'#timeline-sticky-header',
			'.et_extra_other_module',
			'.woocommerce .woocommerce-info',
		),
	);

	return apply_filters( 'extra_dynamic_selectors', $dynamic_selectors );
}

function extra_get_dynamic_selector( $key, $suffix = '' ) {
	$selectors = extra_get_dynamic_selectors();

	if ( ! isset( $selectors[ $key ] ) ) {
		return false;
	}

	$selectors = $selectors[ $key ];

	if ( !empty( $suffix ) ) {
		if ( is_array( $selectors ) ) {
			foreach ( $selectors as $key => $selector ) {
				$selectors[ $key ] = $selector . $suffix;
			}
		} else {
			$selectors = $selectors . $suffix;
		}
	}

	return $selectors;
}

function extra_boxed_layout_background_color( $option_properties ) {
	$boxed_layout = et_get_option( 'boxed_layout' );
	if ( !$boxed_layout ) {
		return false;
	}

	return $option_properties;
}

add_filter( 'extra_print_dynamic_styles-boxed_layout_background_color', 'extra_boxed_layout_background_color' );

function extra_body_classes( $classes ) {
	$classes[] = 'et_extra';

	if ( extra_layout_used() || ( is_et_pb_preview() && isset( $_GET['is_extra_layout'] ) ) ) {
		$classes[] = 'et_extra_layout';
	}

	if ( et_pb_is_pagebuilder_used( get_the_ID() ) ) {
		$classes[] = 'et_pb_pagebuilder_layout';
	}

	if ( true === et_get_option( 'primary_nav_fullwidth', false ) ) {
		$classes[] = 'et_fullwidth_nav';
	}

	if ( true === et_get_option( 'secondary_nav_fullwidth', false ) ) {
		$classes[] = 'et_fullwidth_secondary_nav';
	}

	if ( 'on' === et_get_option( 'extra_fixed_nav', 'on' ) ) {
		$classes[] = 'et_fixed_nav';

		if ( et_get_option( 'hide_nav_until_scroll', false ) ) {
			$classes[] = 'et_hide_nav';
		}

		if ( et_get_option( 'fixed_nav_hide_logo_image', false ) ) {
			$classes[] = 'et_fixed_nav_hide_logo_image';
		}
	} else if ( 'on' !== et_get_option( 'extra_fixed_nav', 'on' ) ) {
		$classes[] = 'et_non_fixed_nav';
	}


	if ( is_page_template( 'page-template-fullwidth.php' ) ) {
		$classes[] = 'et_pb_pagebuilder_fullwidth';
	}


	$classes = array_merge( $classes, (array) extra_customizer_selector_classes( 'body' ) );

	return $classes;
}

add_filter( 'body_class', 'extra_body_classes' );

function extra_print_dynamic_styles_sidebar_width_css_output( $output, $option_properties, $option_value ) {
	if ( $option_value == $option_properties['default'] ) {
		return $output;
	}

	$main_column_with_sidebar = extra_get_dynamic_selector( 'main_column_with_sidebar' );
	$main_column_width = 100 - $option_value;

	$output .= sprintf(
		'%s {
			width: %s%%;
		}',
		esc_attr( $main_column_with_sidebar ),
		esc_attr( $main_column_width )
	);

	return $output;
}

add_filter( 'extra_print_dynamic_styles-sidebar_width-width-css_output', 'extra_print_dynamic_styles_sidebar_width_css_output', 10, 3 );

function et_extra_enqueue_google_maps_api() {
	wp_enqueue_script( 'google-maps-api', esc_url( add_query_arg( array( 'key' => et_pb_get_google_api_key(), 'callback' => 'initMap' ), is_ssl() ? 'https://maps.googleapis.com/maps/api/js' : 'http://maps.googleapis.com/maps/api/js' ) ), array(), '3', true );
}

function extra_register_sidebars() {
	$footer_columns = et_get_option( 'footer_columns', '3' );

	switch ( $footer_columns ) {
		case '4':
			$footer_sidebar_names = array(
				__( 'Footer Sidebar Left', 'extra' ),
				__( 'Footer Sidebar Middle Left', 'extra' ),
				__( 'Footer Sidebar Middle Right', 'extra' ),
				__( 'Footer Sidebar Right', 'extra' ),
			);
			break;

		case '3':
		case '1_4__1_4__1_2':
		case '1_2__1_4__1_4':
		case '1_4__1_2__1_4':
			$footer_sidebar_names = array(
				esc_html__( 'Footer Sidebar Left', 'extra' ),
				esc_html__( 'Footer Sidebar Middle', 'extra' ),
				esc_html__( 'Inactive Footer Sidebar', 'extra' ),
				esc_html__( 'Footer Sidebar Right', 'extra' ),
			);
			break;

		case '1':
			$footer_sidebar_names = array(
				esc_html__( 'Footer Sidebar', 'extra' ),
				esc_html__( 'Inactive Footer Sidebar', 'extra' ),
				esc_html__( 'Inactive Footer Sidebar', 'extra' ),
				esc_html__( 'Inactive Footer Sidebar', 'extra' ),
			);
			break;

		default:
			$footer_sidebar_names = array(
				esc_html__( 'Footer Sidebar Left', 'extra' ),
				esc_html__( 'Inactive Footer Sidebar', 'extra' ),
				esc_html__( 'Inactive Footer Sidebar', 'extra' ),
				esc_html__( 'Footer Sidebar Right', 'extra' ),
			);
			break;
	}

	$sidebars = array(
		array(
			'name' => esc_html__( 'Main Sidebar', 'extra' ),
		),
		array(
			'name' => esc_html__( 'Project Sidebar', 'extra' ),
		),
	);

	$footer_sidebars = array(
		array(
			'name' => $footer_sidebar_names[0],
			'id'   => 'sidebar-footer-1',
		),
		array(
			'name' => $footer_sidebar_names[1],
			'id'   => 'sidebar-footer-2',
		),
		array(
			'name' => $footer_sidebar_names[2],
			'id'   => 'sidebar-footer-3',
		),
		array(
			'name' => $footer_sidebar_names[3],
			'id'   => 'sidebar-footer-4',
		),
	);

	if ( ! is_customize_preview() ) {
		foreach ( extra_footer_columns_visibility() as $key => $visibility ) {
			if ( ! $visibility ) {
				unset( $footer_sidebars[ $key ] );
			}
		}
	}

	foreach ( array_merge( $sidebars, $footer_sidebars ) as $sidebar ) {
		_et_register_sidebar( $sidebar );
	}
}

add_action( 'widgets_init', 'extra_register_sidebars' );

function extra_global_sidebar_location() {
	return et_get_option( 'sidebar_location', 'right' );
}

function extra_footer_columns_visibility() {
	$footer_columns = et_get_option( 'footer_columns', '3' );

	switch ($footer_columns) {
		case '4':
			$footer_columns_visibility = array(
				true,
				true,
				true,
				true,
			);
			break;
		case '3':
		case '1_4__1_4__1_2':
		case '1_2__1_4__1_4':
		case '1_4__1_2__1_4':
			$footer_columns_visibility = array(
				true,
				true,
				false,
				true,
			);
			break;
		case '1':
			$footer_columns_visibility = array(
				true,
				false,
				false,
				false,
			);
			break;
		default:
			$footer_columns_visibility = array(
				true,
				false,
				false,
				true,
			);
			break;
	}

	return apply_filters( 'extra_footer_columns_visibility', $footer_columns_visibility, $footer_columns );
}

function extra_global_sidebar() {
	return et_get_option( 'sidebar', 'Main Sidebar' );
}

function extra_get_social_networks() {
	return apply_filters('extra_social_networks', array(
		'facebook'    => esc_html__( 'Facebook', 'extra' ),
		'twitter'     => esc_html__( 'Twitter', 'extra' ),
		'googleplus'  => esc_html__( 'Google+', 'extra' ),
		'pinterest'   => esc_html__( 'Pinterest', 'extra' ),
		'tumblr'      => esc_html__( 'Tumblr', 'extra' ),
		'stumbleupon' => esc_html__( 'Stumbleupon', 'extra' ),
		'wordpress'   => esc_html__( 'WordPress', 'extra' ),
		'instagram'   => esc_html__( 'Instagram', 'extra' ),
		'dribbble'    => esc_html__( 'Dribbble', 'extra' ),
		'vimeo'       => esc_html__( 'Vimeo', 'extra' ),
		'linkedin'    => esc_html__( 'LinkedIn', 'extra' ),
		'rss'         => esc_html__( 'RSS', 'extra' ),
		'deviantart'  => esc_html__( 'Deviantart', 'extra' ),
		'myspace'     => esc_html__( 'MySpace', 'extra' ),
		'skype'       => esc_html__( 'Skype', 'extra' ),
		'youtube'     => esc_html__( 'Youtube', 'extra' ),
		'picassa'     => esc_html__( 'Picassa', 'extra' ),
		'flickr'      => esc_html__( 'Flickr', 'extra' ),
		'blogger'     => esc_html__( 'Blogger', 'extra' ),
		'spotify'     => esc_html__( 'Spotify', 'extra' ),
		'delicious'   => esc_html__( 'Delicious', 'extra' ),
	));
}

add_filter( 'user_contactmethods', 'extra_get_social_networks', 10, 1 );

function extra_init_walker_nav_menu() {
	class Extra_Walker_Nav_Menu extends Walker_Nav_Menu {

		function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
			if ( !empty( $item->mega_menu ) ) {
				$item->classes = empty( $item->classes ) ? array() : (array) $item->classes;
				$item->classes[] = 'mega-menu menu-item-has-children';
				if ( in_array( $item->mega_menu, array('mega-featured-2', 'mega-featured-3') ) ) {
					$item->classes[] = 'mega-menu-featured ' . esc_attr( $item->mega_menu );
				} else {
					$item->classes[] = 'mega-menu-cols';
				}
				add_filter( 'walker_nav_menu_start_el', array( $this, 'menu_el_mega_item' ), 10, 4 );
			}

			parent::start_el( $output, $item, $depth, $args, $id );

			remove_filter( 'walker_nav_menu_start_el', array( $this, 'menu_el_mega_item' ), 10, 4 );
		}

		function menu_el_mega_item( $item_output, $item, $depth, $args ) {
			if ( $item->object == 'category' ) {
				$args = array(
					'post_type'      => 'post',
					'posts_per_page' => 5,
					'tax_query'      => array(
						array(
							'taxonomy' => 'category',
							'field'    => 'id',
							'terms'    => array( absint( $item->object_id ) ),
							'operator' => 'IN',
						),
					),
				);

				$posts = new WP_Query( $args );

				if ( $item->mega_menu == 'mega-featured-2' ) {
					$item_output .= $this->mega_item_featured_2( $item, $args, $posts );
				} else if ( $item->mega_menu == 'mega-featured-3' ) {
					$item_output .= $this->mega_item_featured_3( $item, $args, $posts );
				}

				wp_reset_postdata();
			}
			return $item_output;
		}

		function mega_item_featured_2( $item, $args, $posts ) {
			$output = '<ul class="sub-menu" style="' . esc_attr( $this->get_mega_item_border_top( $item ) ) . '">';

			for ( $post_count = 0; $post_count <= 1; $post_count++ ) {
				if ( !isset( $posts->posts[ $post_count ] ) ) {
					break;
				}
				$post = $posts->posts[ $post_count ];

				$thumb = et_extra_get_post_thumb(array(
					'post_id' => $post->ID,
					'size'    => 'extra-image-small',
					'a_class' => array('featured-image'),
				));

				$output .= sprintf('
					<li>
						<h2 class="title">%1$s</h2>
						<div class="featured-post">
							%2$s
							<h2>%3$s</h2>
							<div class="post-meta">
								%4$s
							</div>
						</div>
					</li>',
					esc_html__( 'Featured', 'extra' ),
					$thumb,
					esc_html( $post->post_title ),
					et_extra_display_post_meta( array(
						'post_id'       => $post->ID,
						'comment_count' => false,
						'author_link'   => false,
					) )
				);
			}

			$output .= '<li class="menu-item menu-item-has-children">';
			$output .= '<a href="#" class="title">' . esc_html__( 'Recent', 'extra' ) . '</a>';
			$output .= '<ul class="recent-list">';

			for ( $post_count = 2; $post_count <= 4; $post_count++ ) {
				if ( !isset( $posts->posts[ $post_count ] ) ) {
					break;
				}
				$post = $posts->posts[ $post_count ];

				$thumb = et_extra_get_post_thumb(array(
					'post_id'   => $post->ID,
					'size'      => 'extra-image-square-small',
					'img_class' => array('post-thumbnail'),
				));

				$output .= sprintf('
					<li class="recent-post">
						<div class="post-content">
							%1$s
							<div class="post-meta">
								<h3><a href="%2$s">%3$s</a></h3>
								%4$s
							</div>
						</div>
					</li>',
					$thumb,
					get_the_permalink( $post ),
					esc_html( $post->post_title ),
					et_extra_display_post_meta( array(
						'post_id'      => $post->ID,
						'rating_stars' => false,
						'categories'   => false,
					) )
				);
			}

			$output .= '</ul><!-- /.recent-list -->'."\n";

			$output .= '</li><!-- /.menu_item -->'."\n";

			$output .= '</ul><!-- /.sub-menu -->'."\n";

			return $output;
		}

		function mega_item_featured_3( $item, $args, $posts ) {
			$output = '<ul class="sub-menu" style="' . esc_attr( $this->get_mega_item_border_top( $item ) ) . '">';
			$post_count = 0;
			foreach ( $posts->posts as $post ) {
				if ( $post_count >= 3 ) {
					break;
				}
				$thumb = et_extra_get_post_thumb(array(
					'post_id' => $post->ID,
					'size'    => 'extra-image-small',
					'a_class' => array('featured-image'),
				));

				$output .= sprintf('
					<li>
						<h2 class="title">%1$s</h2>
						<div class="featured-post">
							%2$s
							<h2>%3$s</h2>
							<div class="post-meta">
								%4$s
							</div>
						</div>
					</li>',
					esc_html__( 'Featured', 'extra' ),
					$thumb,
					esc_html( $post->post_title ),
					et_extra_display_post_meta( array(
						'post_id'       => $post->ID,
						'comment_count' => false,
						'author_link'   => false,
					) )
				);

				$post_count++;
			}
			$output .= '</ul>';

			return $output;
		}

		function get_mega_item_border_top( $item ) {
			if ( ! isset( $item->object_id ) ) {
				return "";
			}

			$category_color = extra_get_category_color( $item->object_id );

			if ( ! $category_color ) {
				return "";
			}

			return "border-top-color: {$category_color};";
		}

	}
}

add_action( 'init', 'extra_init_walker_nav_menu' );

function extra_setup_nav_menu_item( $menu_item ) {
	$menu_item->mega_menu = get_post_meta( $menu_item->ID, '_menu_item_mega_menu', true );
	return $menu_item;
}

add_filter( 'wp_setup_nav_menu_item', 'extra_setup_nav_menu_item', 10, 1 );

function extra_add_mobile_navigation(){
	printf(
		'<div id="et-mobile-navigation">
			<span class="show-menu">
				<div class="show-menu-button">
					<span></span>
					<span></span>
					<span></span>
				</div>
				<p>%1$s</p>
			</span>
			<nav>
			</nav>
		</div> <!-- /#et-mobile-navigation -->',
		esc_html__( 'Select Page', 'extra' )
	);
}

add_action( 'et_header_top', 'extra_add_mobile_navigation' );

function extra_format_url( $url ) {
	$url = str_replace( array( 'http://', 'https://', '//' ), '', $url );
	$url = str_replace( 'www.', '', $url );
	$url = 'http://www.' . $url;
	return esc_url( $url );
}

function extra_global_accent_color() {
	// todo: use this everywhere
	$color = et_get_option( 'accent_color', '#00a8ff' );

	if ( 0 !== strpos( $color, "#" ) ) {
		$color = "#" . $color;
	}

	return $color;
}

function extra_is_customizer_request() {
	global $wp_customize;

	return (bool) is_a( $wp_customize, 'WP_Customize_Manager' ) && $wp_customize->is_preview();
}

function et_parse_args( $args, $defaults = '', $disallow_empty = true ) {
	$_args = wp_parse_args( $args, $defaults );

	if ( $disallow_empty ) {
		foreach ( $_args as $key => $value) {
			if ( empty( $_args[ $key ] ) ) {
				if ( !empty( $defaults[ $key ] ) ) {
					$_args[ $key ] = $defaults[ $key ];
				}
			}
		}
	}

	return $_args;
}

function et_theme_epanel_reminder() {
	global $shortname;

	$documentation_url         = 'http://www.elegantthemes.com/gallery/extra/documentation/';
	$documentation_option_name = $shortname . '_documentation_message';

	if ( false === et_get_option( $shortname . '_logo' ) && false === et_get_option( $documentation_option_name ) ) {
		$message = sprintf(
			__( 'Welcome to Extra! Before diving in to your new theme, please visit the <a style="color: #fff; font-weight: bold;" href="%1$s" target="_blank">Extra Documentation</a> page for access to dozens of in-depth tutorials.', 'extra' ),
			esc_url( $documentation_url )
		);

		printf(
			'<div class="notice is-dismissible" style="background-color: #46EA9E; color: #fff; border-left: none;">
				<p>%1$s</p>
			</div>',
			$message
		);

		et_update_option( $documentation_option_name, 'triggered' );
	}
}

function extra_add_customizer_admin_menu() {
	if ( ! current_user_can( 'customize' ) ) {
		return;
	}

	global $wp_admin_bar;

	$wp_admin_bar->remove_menu( 'customize' );

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = urlencode( esc_url( $current_url ) );
	$customize_url = add_query_arg( array( 'url' => $current_url ), wp_customize_url() );

	// add Theme Customizer admin menu only if it's enabled for current user
	if ( et_pb_is_allowed( 'theme_customizer' ) ) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id'     => 'customize-extra-theme',
			'title'  => esc_html__( 'Theme Customizer', 'extra' ),
			'href'   => add_query_arg( array( 'et_customizer_option_set' => 'theme' ), $customize_url ),
			'meta'   => array(
				'class' => 'hide-if-no-customize',
			),
		) );
	}

	// add Module Customizer admin menu only if it's enabled for current user
	if ( et_pb_is_allowed( 'module_customizer' ) ) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id'     => 'customize-extra-module',
			'title'  => esc_html__( 'Module Customizer', 'extra' ),
			'href'   => add_query_arg( array( 'et_customizer_option_set' => 'module' ), $customize_url ),
			'meta'   => array(
				'class' => 'hide-if-no-customize',
			),
		) );
	}
}

add_action( 'admin_bar_menu', 'extra_add_customizer_admin_menu', 999 );

/**
 * Register theme and modules Cutomizer portability.
 *
 * @since To define
 *
 * @return bool Always return true.
 */
function extra_register_customizer_portability() {
	global $options;

	// Make sure the Portability is loaded.
	et_core_load_component( 'portability' );

	// Load ePanel options.
	et_load_core_options();

	// Exclude ePanel options.
	$exclude = array();

	foreach ( $options as $option ) {
		if ( isset( $option['id'] ) ) {
			$exclude[ $option['id'] ] = true;
		}
	}

	// Register the portability.
	et_core_portability_register( 'et_extra_mods', array(
		'name'    => esc_html__( 'Extra Customizer Settings', 'Extra' ),
		'type'    => 'options',
		'target'  => 'et_extra',
		'exclude' => $exclude,
		'view'    => is_customize_preview(),
	) );
}
add_action( 'admin_init', 'extra_register_customizer_portability' );

/**
 * Register theme and modules Cutomizer portability link.
 *
 * @since To define
 *
 * @return bool Always return true.
 */
function extra_customizer_link() {
	if ( is_customize_preview() ) {
		echo et_core_portability_link( 'et_extra_mods', array( 'class' => 'customize-controls-close' ) );
	}
}
add_action( 'customize_controls_print_footer_scripts', 'extra_customizer_link' );

function et_register_updates_component() {
	et_core_enable_automatic_updates( get_template_directory_uri(), ET_CORE_VERSION );
}
add_action( 'admin_init', 'et_register_updates_component' );
