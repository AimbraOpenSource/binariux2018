<?php

/**
 * Plugin Name: AdSense Integration WP QUADS
 * Plugin URI: https://wordpress.org/plugins/quick-adsense-reloaded/
 * Description: Insert Google AdSense or any Ads code into your website. A fork of Quick AdSense
 * Author: Rene Hermenau, WP-Staging
 * Author URI: https://wordpress.org/plugins/quick-adsense-reloaded/
 * Version: 1.6.0
 * Text Domain: quick-adsense-reloaded
 * Domain Path: languages
 * Credits: WP QUADS - Quick AdSense Reloaded is a fork of Quick AdSense
 *
 * WP QUADS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WP QUADS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package QUADS
 * @category Core
 * @author René Hermenau
 * @version 0.9.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
   exit;

// Plugin version
if( !defined( 'QUADS_VERSION' ) ) {
   define( 'QUADS_VERSION', '1.6.0' );
}

// Plugin name
if( !defined( 'QUADS_NAME' ) ) {
   define( 'QUADS_NAME', 'WP QUADS - Quick AdSense Reloaded' );
}

// Debug
if( !defined( 'QUADS_DEBUG' ) ) {
   define( 'QUADS_DEBUG', false );
}

// Files that needs to be loaded early
if( !class_exists( 'QUADS_Utils' ) ) {
   require dirname( __FILE__ ) . '/includes/quads-utils.php';
}

// Define some globals
$visibleContentAds = 0; // Amount of ads which are shown
$visibleShortcodeAds = 0; // Number of active ads which are shown via shortcodes
$visibleContentAdsGlobal = 0; // Number of active ads which are shown in the_content
$ad_count_custom = 0; // Number of active custom ads which are shown on the site
$ad_count_widget = 0; // Number of active ads in widgets
$AdsId = array(); // Array of active ad id's
$maxWidgets = 10; // number of widgets


if( !class_exists( 'QuickAdsenseReloaded' ) ) :

   /**
    * Main QuickAdsenseReloaded Class
    *
    * @since 1.0.0
    */
   final class QuickAdsenseReloaded {
      /** Singleton ************************************************************ */

      /**
       * @var QuickAdsenseReloaded The one and only QuickAdsenseReloaded
       * @since 1.0
       */
      private static $instance;

      /**
       * QUADS HTML Element Helper Object
       *
       * @var object
       * @since 2.0.0
       */
      public $html;

      /* QUADS LOGGER Class
       * 
       */
      public $logger;

      /**
       * Main QuickAdsenseReloaded Instance
       *
       * Insures that only one instance of QuickAdsenseReloaded exists in memory at any one
       * time. Also prevents needing to define globals all over the place.
       *
       * @since 1.0
       * @static
       * @static var array $instance
       * @uses QuickAdsenseReloaded::setup_constants() Setup the constants needed
       * @uses QuickAdsenseReloaded::includes() Include the required files
       * @uses QuickAdsenseReloaded::load_textdomain() load the language files
       * @see QUADS()
       * @return The one true QuickAdsenseReloaded
       */
      public static function instance() {
         if( !isset( self::$instance ) && !( self::$instance instanceof QuickAdsenseReloaded ) ) {
            self::$instance = new QuickAdsenseReloaded;
            self::$instance->setup_constants();
            self::$instance->includes();
            self::$instance->load_textdomain();
            self::$instance->load_hooks();
            self::$instance->logger = new quadsLogger( "quick_adsense_log_" . date( "Y-m-d" ) . ".log", quadsLogger::INFO );
            self::$instance->html = new QUADS_HTML_Elements();
         }
         return self::$instance;
      }

      /**
       * Throw error on object clone
       *
       * The whole idea of the singleton design pattern is that there is a single
       * object therefore, we don't want the object to be cloned.
       *
       * @since 1.0
       * @access protected
       * @return void
       */
      public function __clone() {
         // Cloning instances of the class is forbidden
         _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'QUADS' ), '1.0' );
      }

      /**
       * Disable unserializing of the class
       *
       * @since 1.0
       * @access protected
       * @return void
       */
      public function __wakeup() {
         // Unserializing instances of the class is forbidden
         _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'QUADS' ), '1.0' );
      }

      /**
       * Setup plugin constants
       *
       * @access private
       * @since 1.0
       * @return void
       */
      private function setup_constants() {
         global $wpdb;

         // Plugin Folder Path
         if( !defined( 'QUADS_PLUGIN_DIR' ) ) {
            define( 'QUADS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
         }

         // Plugin Folder URL
         if( !defined( 'QUADS_PLUGIN_URL' ) ) {
            define( 'QUADS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
         }

         // Plugin Root File
         if( !defined( 'QUADS_PLUGIN_FILE' ) ) {
            define( 'QUADS_PLUGIN_FILE', __FILE__ );
         }
      }

      /**
       * Include required files
       *
       * @access private
       * @since 1.0
       * @return void
       */
      private function includes() {
         global $quads_options;

         require_once QUADS_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
         $quads_options = quads_get_settings();
         require_once QUADS_PLUGIN_DIR . 'includes/post_types.php';
         require_once QUADS_PLUGIN_DIR . 'includes/user_roles.php';
         require_once QUADS_PLUGIN_DIR . 'includes/widgets.php';
         require_once QUADS_PLUGIN_DIR . 'includes/template-functions.php';
         require_once QUADS_PLUGIN_DIR . 'includes/class-quads-license-handler.php';
         require_once QUADS_PLUGIN_DIR . 'includes/logger.php';
         require_once QUADS_PLUGIN_DIR . 'includes/class-quads-html-elements.php';
         require_once QUADS_PLUGIN_DIR . 'includes/shortcodes.php';
         require_once QUADS_PLUGIN_DIR . 'includes/api.php';
         require_once QUADS_PLUGIN_DIR . 'includes/render-ad-functions.php';
         require_once QUADS_PLUGIN_DIR . 'includes/scripts.php';
         require_once QUADS_PLUGIN_DIR . 'includes/automattic-amp-ad.php';
         require_once QUADS_PLUGIN_DIR . 'includes/helper-functions.php';
         require_once QUADS_PLUGIN_DIR . 'includes/conditions.php';
         require_once QUADS_PLUGIN_DIR . 'includes/frontend-checks.php';
         require_once QUADS_PLUGIN_DIR . 'includes/Cron/Cron.php';


         if( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            require_once QUADS_PLUGIN_DIR . 'includes/admin/add-ons.php';
            require_once QUADS_PLUGIN_DIR . 'includes/admin/admin-actions.php';
            require_once QUADS_PLUGIN_DIR . 'includes/admin/admin-footer.php';
            require_once QUADS_PLUGIN_DIR . 'includes/admin/admin-pages.php';
            require_once QUADS_PLUGIN_DIR . 'includes/admin/plugins.php';
            require_once QUADS_PLUGIN_DIR . 'includes/admin/welcome.php';
            require_once QUADS_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
            require_once QUADS_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php';
            //require_once QUADS_PLUGIN_DIR . 'includes/install.php';
            require_once QUADS_PLUGIN_DIR . 'includes/admin/tools.php';
            require_once QUADS_PLUGIN_DIR . 'includes/meta-boxes.php';
            require_once QUADS_PLUGIN_DIR . 'includes/quicktags.php';
            require_once QUADS_PLUGIN_DIR . 'includes/admin/admin-notices.php';
            require_once QUADS_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
         }
      }

      public function load_hooks() {
         if( is_admin() && quads_is_plugins_page() ) {
            add_filter( 'admin_footer', 'quads_add_deactivation_feedback_modal' );
         }
      }

      /**
       * Loads the plugin language files
       *
       * @access public
       * @since 1.0
       * @return void
       */
      public function load_textdomain() {
         // Set filter for plugin's languages directory
         $quads_lang_dir = dirname( plugin_basename( QUADS_PLUGIN_FILE ) ) . '/languages/';
         $quads_lang_dir = apply_filters( 'quads_languages_directory', $quads_lang_dir );

         // Traditional WordPress plugin locale filter
         $locale = apply_filters( 'plugin_locale', get_locale(), 'quick-adsense-reloaded' );
         $mofile = sprintf( '%1$s-%2$s.mo', 'quick-adsense-reloaded', $locale );

         // Setup paths to current locale file
         $mofile_local = $quads_lang_dir . $mofile;
         $mofile_global = WP_LANG_DIR . '/quads/' . $mofile;
         //echo $mofile_local;
         if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/quads folder
            load_textdomain( 'quick-adsense-reloaded', $mofile_global );
         } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/quick-adsense-reloaded/languages/ folder
            load_textdomain( 'quick-adsense-reloaded', $mofile_local );
         } else {
            // Load the default language files
            load_plugin_textdomain( 'quick-adsense-reloaded', false, $quads_lang_dir );
         }
      }

      /*
       * Activation function fires when the plugin is activated.  
       * Checks first if multisite is enabled
       * @since 1.0.0
       * 
       */

      public static function activation( $networkwide ) {
         global $wpdb;

         if( function_exists( 'is_multisite' ) && is_multisite() ) {
            // check if it is a network activation - if so, run the activation function for each blog id
            if( $networkwide ) {
               $old_blog = $wpdb->blogid;
               // Get all blog ids
               $blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
               foreach ( $blogids as $blog_id ) {
                  switch_to_blog( $blog_id );
                  QuickAdsenseReloaded::during_activation();
               }
               switch_to_blog( $old_blog );
               return;
            }
         }
         QuickAdsenseReloaded::during_activation();
      }

      /**
       * This function is fired from the activation method.
       *
       * @since 2.1.1
       * @access public
       *
       * @return void
       */
      public static function during_activation() {
         
         // Add cron event   
         require_once plugin_dir_path( __FILE__ ) . 'includes/Cron/Cron.php';
         $cron = new quadsCron();
         $cron->schedule_event();

         // Add Upgraded From Option
         $current_version = get_option( 'quads_version' );
         if( $current_version ) {
            update_option( 'quads_version_upgraded_from', $current_version );
         }
         // First time installation
         // Get all settings and update them only if they are empty
         $quads_options = get_option( 'quads_settings' );
         if( !$quads_options ) {
            $quads_options['post_types'] = array('post', 'page');
            $quads_options['visibility']['AppHome'] = "1";
            $quads_options['visibility']['AppCate'] = "1";
            $quads_options['visibility']['AppArch'] = "1";
            $quads_options['visibility']['AppTags'] = "1";
            $quads_options['quicktags']['QckTags'] = "1";

            update_option( 'quads_settings', $quads_options );
         }

         // Update the current version
         //update_option( 'quads_version', QUADS_VERSION );
         // Add plugin installation date and variable for rating div
         add_option( 'quads_install_date', date( 'Y-m-d h:i:s' ) );
         add_option( 'quads_rating_div', 'no' );
         add_option( 'quads_show_theme_notice', 'yes' );

         // Add the transient to redirect (not for multisites)
         set_transient( 'quads_activation_redirect', true, 3600 );
      }

   }

   endif; // End if class_exists check

/**
 * The main function responsible for returning the one true QuickAdsenseReloaded
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: $QUADS = QUADS();
 *
 * @since 2.0.0
 * @return object The one true QuickAdsenseReloaded Instance
 */

/**
 * Populate the $quads global with an instance of the QuickAdsenseReloaded class and return it.
 *
 * @return $quads a global instance class of the QuickAdsenseReloaded class.
 */
function quads_loaded() {

   global $quads;

   if( !is_null( $quads ) ) {
      return $quads;
   }

   $quads_instance = new QuickAdsenseReloaded;
   $quads = $quads_instance->instance();
   return $quads;
}

add_action( 'plugins_loaded', 'quads_loaded' );

/**
 * The activation hook is called outside of the singleton because WordPress doesn't
 * register the call from within the class hence, needs to be called outside and the
 * function also needs to be static.
 */
register_activation_hook( __FILE__, array('QuickAdsenseReloaded', 'activation') );

/**
 * Check if pro version is installed and active
 */
function quads_is_pro_active() {
   $needle = 'wp-quads-pro';
   $plugins = get_option( 'active_plugins', array() );
   foreach ( $plugins as $key => $value ) {
      if( strpos( $value, $needle ) !== false  ) {
         return true;
      }
   }
   return false;
}


/**
 * Check if advanced settings are available
 * 
 * @return boolean
 */
function quads_is_advanced() {
   if( function_exists( 'quads_is_active_pro' ) ) {
      return quads_is_active_pro();
   } else {
      return quads_is_active_deprecated();
   }
   return false;
}

/**
 * Check if wp quads pro is active and installed
 * 
 * @deprecated since version 1.3.0
 * @return boolean
 */
function quads_is_active_deprecated() {

   include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
   $plugin = 'wp-quads-pro/wp-quads-pro.php';

   if( is_plugin_active( $plugin ) ) {
      return true;
   }

   return false;
}
