<?php
// Prevent file from being loaded directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Extra Theme
 *
 * functions.php
 *
 * Load & setup theme files/functions
 */

define( 'EXTRA_LAYOUT_POST_TYPE', 'layout' );
define( 'EXTRA_PROJECT_POST_TYPE', 'project' );
define( 'EXTRA_PROJECT_CATEGORY_TAX', 'project_category' );
define( 'EXTRA_PROJECT_TAG_TAX', 'project_tag' );
define( 'EXTRA_RATING_COMMENT_TYPE', 'rating' );

$et_template_directory = get_template_directory();

// Load Framework
require $et_template_directory . '/framework/functions.php';

// Load theme core functions
require $et_template_directory . '/includes/core.php';
require $et_template_directory . '/includes/plugins-woocommerce-support.php';
require $et_template_directory . '/includes/plugins-seo-support.php';
require $et_template_directory . '/includes/activation.php';
require $et_template_directory . '/includes/customizer.php';
require $et_template_directory . '/includes/builder-integrations.php';
require $et_template_directory . '/includes/layouts.php';
require $et_template_directory . '/includes/template-tags.php';
require $et_template_directory . '/includes/ratings.php';
require $et_template_directory . '/includes/projects.php';
require $et_template_directory . '/includes/widgets.php';
require $et_template_directory . '/includes/et-social-share.php';

// Load admin only resources
if ( is_admin() ) {
	require $et_template_directory . '/includes/admin/admin.php';
	require $et_template_directory . '/includes/admin/category.php';
}
