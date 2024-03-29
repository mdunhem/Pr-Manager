<?php
/**
 * Constants used by this plugin
 * 
 * @package Press Release Manager
 * 
 * @author Mike Dunhem
 * @version 1.1
 * @since 1.0
 */

// The current version of this plugin
if(!defined('PR_MANAGER_VERSION')) {
	define('PR_MANAGER_VERSION', 1.1);
}

// The directory the plugin resides in
if(!defined('PR_MANAGER_DIRNAME')) {
	define('PR_MANAGER_DIRNAME', dirname(dirname(__FILE__)));
}

// The URL path of this plugin
if(!defined('PR_MANAGER_URLPATH')) {
	define('PR_MANAGER_URLPATH', WP_PLUGIN_URL . "/" . plugin_basename(PR_MANAGER_DIRNAME));
}

// The database table name
if(!defined('PR_DB_TABLE_NAME')) {
	define('PR_DB_TABLE_NAME', $wpdb->prefix . 'pr_manager');
}
