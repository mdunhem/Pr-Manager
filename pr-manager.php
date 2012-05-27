<?php
/*
Plugin Name: Press Release Manager
Plugin URI: http://www.arielway.com
Description: Admin menu page plugin for uploading Ariel Way, Inc. Press Releases to website.
Author: Mikael Dunhem
Version: 1.1
Author URI: http://www.arielway.com
License: GPL2

Copyright 2012.  Mikael Dunhem  (email : mikael.dunhem@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Include constants file
require_once( dirname( __FILE__ ) . '/include/constants.php' );

// Create new instance of Pr_Manager and hand off control
if( !isset( $pr_manager ) ) {
	Pr_Manager::instance();
	register_uninstall_hook(__FILE__, array('Pr_Manager', 'uninstall'));
}

class Pr_Manager {
	protected $options;
	
	public function __construct($options = null) {
		global $wpdb;
		// Set up class variables
		$this->options = array(
				'version' => PR_MANAGER_VERSION,
				'title' => 'Upload New Press Release',
				'sub-title' => 'New Press Release',
				'page-name' => 'pr-manager',
				'company-name' => 'Ariel Way, Inc.',
				'db-table-name' => PR_DB_TABLE_NAME
		);
		if ($options) {
				$this->options = array_replace_recursive($this->options, $options);
		}
		
		$this->add_actions();
	}
	
	protected function add_actions() {		
		// Register all Stylesheets for this plugin
		$this->register_styles();
		// Register all JavaScripts for this plugin
		$this->register_scripts();
		// Add admin_init hook		
		add_action('admin_init', array(&$this, 'init'));
		// Add admin_menu hook
		add_action('admin_menu', array(&$this, 'menu'));
	}
	
	/**
	 *	Installation function, uses dbDelta function to safely create or update
	 *	the database table.
	 *	Is called by 'init' function if plugin has not been installed or
	 *	if this is an updated version.
	 *
	 */
	protected function install() {
		$sql = "CREATE TABLE " . $this->options['db-table-name'] . " (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			year text NOT NULL,
			date text NOT NULL,
			title text NOT NULL,
			location text NOT NULL,
			filename text NOT NULL,
			PRIMARY KEY  (id)
		);";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		update_option('pr_manager_version', $this->options['version']);
	}
	
	static function uninstall() {
		// TODO: create uninstall method
		delete_option('pr_manager_version');
		//$sql = $wpdb->query('DROP TABLE IF EXISTS ' . $this->options['db-table-name'] . ';');
	}
	
	public function init() {
		// Check if current version is correct, otherwise run the install function
		$current_installed_version = get_option('pr_manager_version');
		if((!$current_installed_version) || ($current_installed_version > $this->options['version'])) {
			$this->install();
		}
	}
	
	public function menu() {
		$page = add_media_page($this->options['title'], $this->options['sub-title'], 'upload_files', $this->options['page-name'], array(&$this, 'display'));
		add_action('admin_print_styles-' . $page, array(&$this, 'load_stylesheet'));
		add_action('admin_print_scripts-' . $page, array(&$this, 'load_scripts'));
	}
	
	protected function register_styles() {
		// Register stylesheet
		wp_register_style('pr_manager_style_sheet', PR_MANAGER_URLPATH . '/css/bootstrap.css', array(), $this->options['version']);
	}
		
	protected function register_scripts() {
		// Register scripts
		wp_register_script('pr-jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', array(), true);
		wp_register_script('pr-jquery-ui-widget', PR_MANAGER_URLPATH . '/js/jquery.ui.widget.js', array('pr-jquery'), true);
		wp_register_script('pr-jquery-template', PR_MANAGER_URLPATH . '/js/tmpl.js', array('pr-jquery'), true);
		wp_register_script('pr-bootstrap', PR_MANAGER_URLPATH . '/js/bootstrap.min.js', array('pr-jquery'), true);
		wp_register_script('pr-jquery-iframe-transport', PR_MANAGER_URLPATH . '/js/jquery.iframe-transport.js', array('pr-jquery'), true);
		wp_register_script('pr-jquery-fileupload', PR_MANAGER_URLPATH . '/js/jquery.fileupload.js', array('pr-jquery', 'pr-jquery-ui-widget'), true);
		wp_register_script('pr-jquery-fileupload-ip', PR_MANAGER_URLPATH . '/js/jquery.fileupload-ip.js', array('pr-jquery'), true);
		wp_register_script('pr-jquery-fileupload-ui', PR_MANAGER_URLPATH . '/js/jquery.fileupload-ui.js', array('pr-jquery'), true);
		wp_register_script('pr-locale', PR_MANAGER_URLPATH . '/js/locale.js', array('pr-jquery'), true);
		wp_register_script('pr-main', PR_MANAGER_URLPATH . '/js/main.js', array('pr-jquery'), true);
	}
	
	public function load_stylesheet() {
		wp_enqueue_style('pr_manager_style_sheet');
	}
	
	public function load_scripts() {
		wp_enqueue_script('pr-jquery');
		wp_enqueue_script('pr-jquery-ui-widget');
		wp_enqueue_script('pr-jquery-template');
		wp_enqueue_script('pr-bootstrap');
		wp_enqueue_script('pr-jquery-iframe-transport');
		wp_enqueue_script('pr-jquery-fileupload');
		wp_enqueue_script('pr-jquery-fileupload-ip');
		wp_enqueue_script('pr-jquery-fileupload-ui');
		wp_enqueue_script('pr-locale');
		wp_enqueue_script('pr-main');
	}
	
	/**
	 *	Display the page
	 *
	 */
	public function display() {		
		if ( !current_user_can( 'upload_files' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>

		  <div class="wrap" >
			<!-- The file upload form used as target for the file upload widget -->
			<div class="container-fluid">
			  <div class="row-fluid">
				<div class="span12">
				  <div class="well">
					<div class="row-fluid">
					  <div class="span12">
						<div class="span7">
						  <?php screen_icon('edit-pages'); ?>
						  <h2><?php echo esc_html( $this->options['title'] ); ?></h2>
						</div>
						<div class="span6">
						  <h2 class="pull-right"><?php echo esc_html($this->options['company-name']); ?></h2>
						</div>
					  </div>
					</div>
				  </div>
				  <form id="fileupload" name="fileupload" action="../wp-content/plugins/pr-manager/upload/" method="POST" enctype="multipart/form-data" class="form-inline" >
					<div class="row-fluid fileupload-buttonbar">
					  <div class="span12">
						<div class="row-fluid">
						  <div class="span7">
							<div class="control-group">
							  <div class="controls">
								<div class="g-button-container">
								  <span class="g-button blue fileinput-button">
									<i class="icon-plus icon-white"></i>
									<span>Add files...</span>
									<input type="file" name="files[]" accept="application/pdf" multiple="yes" />
								  </span>
								  <div class="g-button-group">
									<button type="submit" class="g-button start">
									  <i class="icon-upload"></i>
									  <span>Start upload</span>
									</button>
									<button type="reset" class="g-button cancel">
									  <i class="icon-ban-circle"></i>
									  <span>Cancel upload</span>
									</button>
									<button type="button" class="g-button red delete">
									  <i class="icon-trash icon-white"></i>
									  <span>Delete</span>
									</button>
								  </div>
								  <label class="checkbox">
									<input type="checkbox" class="toggle">
									Select All
								  </label>
								</div>
							  </div>
							</div>
						  </div>
						  <div class="span5">
							<!-- The global progress bar -->
							<div class="progress progress-success progress-striped active fade">
							  <div class="bar" style="width:0%;"></div>
							</div>
						  </div>
						</div>
					  </div>
					</div>
					<br />
					<div class="row-fluid">
					  <div class="span12">
						<!-- The table listing the files available for upload/download -->
						<table class="table table-striped">
							<thead>
								<tr>
									<th>File Name:</th>
									<th>File Description:</th>
									<th>Date:</th>
									<th>File Size:</th>
								</tr>
							</thead>
							<tbody class="files"></tbody>
						</table>
					  </div>
					</div>
				  </form>
				</div>
			  </div>
			  <!-- The template to display files available for download -->
			  <script id="template-download" type="text/x-tmpl">
			  {% for (var i=0, file; file=o.files[i]; i++) { %}
				  <tr class="template-download fade">
					  {% if (file.error) { %}
						  <td></td>
						  <td class="name"><span>{%=file.name%}</span></td>
						  <td class="description"><span>{%=file.description%}</span></td>
						  <td class="date"><span>{%=file.date%}</span></td>
						  <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
						  <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
					  {% } else { %}
						  <td class="name">
							  <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a>
						  </td>
						  <td class="description">
												<!--// TODO: Add ability to edit description, with "Update" button
							<input type="text" name="description[]" class="span3" value="{%=file.description%}" required>
							<span class="help-block">Change Press Release Title Here</span>-->
												<span>{%=file.description%}</span>
						  </td>
						  <td class="date">
												<!--// TODO: Add ability to edit date, with "Update" button
							<input type="date" name="date[]" class="span2" value="{%=file.date%}" required>
							<span class="help-block">Press Release Date</span>-->
												<span>{%=file.date%}</span>
						  </td>
						  <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
						  <td colspan="2"></td>
					  {% } %}
									<!--TODO: Add "Update" button
					  <td class="update">
						<button class="g-button green" type="" name="update">
						  <i class="icon-refresh icon-white"></i>
						  Update
						</button>
					  </td>-->
					  <td class="delete">
						  <button class="g-button red" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
							  <i class="icon-trash icon-white"></i>
							  <span>{%=locale.fileupload.destroy%}</span>
						  </button>
						  <input type="checkbox" name="delete" value="1">
					  </td>
				  </tr>
			  {% } %}
			  </script>
			  <!-- The template to display files available for upload -->
			  <script id="template-upload" type="text/x-tmpl">
			  {% for (var i=0, file; file=o.files[i]; i++) { %}
				  <tr class="template-upload fade">
					  <td class="name">
						<span>{%=file.name%}</span>
					  </td>
					  <td class="description">
						<input type="text" name="description[]" class="span3" required>
						<span class="help-block">Enter Press Release Title Here</span>
					  </td>
					  <td class="date">
						<input type="date" name="date[]" class="span2" required>
						<span class="help-block">Press Release Date</span>
					  </td>
					  <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
					  {% if (file.error) { %}
						  <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
					  {% } else if (o.files.valid && !i) { %}
						  <td>
							  <div class="progress progress-success progress-striped active"><div class="bar" style="width:0%;"></div></div>
						  </td>
						  <td class="start">{% if (!o.options.autoUpload) { %}
							  <button class="g-button blue">
								  <i class="icon-upload icon-white"></i>
								  <span>{%=locale.fileupload.start%}</span>
							  </button>
						  {% } %}</td>
					  {% } else { %}
						  <td colspan="2"></td>
					  {% } %}
					  <td class="cancel">{% if (!i) { %}
						  <button class="g-button red">
							  <i class="icon-ban-circle icon-white"></i>
							  <span>{%=locale.fileupload.cancel%}</span>
						  </button>
					  {% } %}</td>
				  </tr>
			  {% } %}
			  </script>
			  <!-- The XDomainRequest Transport is included for cross-domain file deletion for IE8+ -->
			  <!--[if gte IE 8]><script src="js/cors/jquery.xdr-transport.js"></script><![endif]-->
			</div>
		  </div>
	<?php
	}
	
	 /**
		* Initialization function to hook into the WordPress init action
		* 
		* Instantiates the class on a global variable and sets the class, actions
		* etc. up for use.
		*/
	 static function instance() {
		 global $pr_manager;
		 
		 // Only instantiate the Class if it hasn't been already
		 if(!isset($pr_manager)) {
				$pr_manager = new Pr_Manager();
		 }
	 }


}