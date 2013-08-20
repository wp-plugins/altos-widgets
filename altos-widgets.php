<?php
/*
Copyright: © 2010 AltosResearch.com ( coded in the USA )
<mailto:support@altosresearch.com> <http://www.altosresearch.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License.
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
Version: 1.3.0
Stable tag: trunk
Tested up to: 3.6
Requires at least: 2.8.4
Plugin Name: Altos Widgets

Author: AltosResearch.com
Contributors: AltosResearch
Author URI: http://www.altosresearch.com/
License: http://www.gnu.org/licenses/gpl-2.0.txt
Plugin URI: http://blog.altosresearch.com/ready-four-new-wordpress-plugins-for-real-estate-data/
Tags: widget, widgets, altos, altos research, altosresearch, real estate, property, charts, graphs
Description: Easily embed tables and charts of current Altos Research real estate statistics on your website.
*/
/*
Direct access denial.
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit;
/*
Class for Altos Widgets.
*/
class Altos_Widgets
	{
		var $s, $sz, $rt, $ra, $q, $ts, $l, $dir_url;
		/**/
		var $webservice = "http://www.altosresearch.com/altos/app";
		/*
		Constructor.
		*/
		function Altos_Widgets ()
			{
				$this->__construct ();
			}
		/**/
		function __construct ()
			{
				add_action ("admin_menu", array (&$this, "on_admin_menu"));
				add_action ("admin_notices", array (&$this, "on_admin_notices"));
				add_action ("widgets_init", array (&$this, "on_widgets_init"));
				add_action ("wp_print_scripts", array (&$this, "on_wp_print_scripts"));
				add_action ("wp_head", array (&$this, "on_wp_head"));
				/**/
				$this->dir_url = WP_CONTENT_URL . preg_replace ("/^(.*?)(\/" . preg_quote (basename (WP_CONTENT_DIR), "/") . ")/", "", dirname (__FILE__));
				/**/
				$this->s[0] = array ("median_price" => "Median Price", "median_inventory" => "Inventory", "mean_dom" => "Average Days on Market", "median_per_sqft" => "Median Price Per Square Foot", "median_market_heat" => "Median Market Action Index ");
				$this->s[1] = array ("Price" => "Price", "zip_region_inventory" => "Inventory", "dom" => "Days on Market", "price_per_sqft" => "Price Per Square Foot", "market_action" => "Market Action Index ");
				/**/
				$this->sz = array ("t" => "Tiny [150px X 100px]", "b" => "Blog [180px X 120px]", "s" => "Small [240px X 160px]", "w" => "Tower [180px X 200px]", "i" => "Smallish [360px X 240px]", "m" => "Medium [480px X 320px]", "l" => "Large [600px X 400px]", "a" => "Large Landscape [520px X 240px]", "g" => "Small Landscape [340px X 160px]");
				/**/
				$this->rt[0] = array ("sf" => "Single Family Homes", "mf" => "Condos/Townhomes");
				$this->rt[1] = array ("sf" => "Single Family Homes", "mf" => "Condos/Townhomes", "sf,mf" => "SFH vs. Condos");
				/**/
				$this->ra = array ("a" => "7-day", "c" => "90-day", "a,c" => "7-day vs. 90-day");
				/**/
				$this->q = array ("a" => "All Quartiles Combined", "t" => "Top Quartile", "u" => "Upper Quartile", "l" => "Lower Quartile", "b" => "Bottom Quartile", "t,b" => "Top vs. Bottom Quartile", "t,u,l,b" => "Four Quartile Comparison");
				/**/
				$this->ts = array ("e" => "1-year", "f" => "2-year", "g" => "3-year", "z" => "All Available Data");
				/**/
				$this->l = array ("b" => "Narrow", "f" => "Wide");
			}
		/*
		Widget initializer.
		*/
		function on_widgets_init ()
			{
				include_once dirname (__FILE__) . "/altos-stat-table.php";
				register_widget ("Altos_Widgets_stat_table_widget");
				/**/
				include_once dirname (__FILE__) . "/altos-charts.php";
				register_widget ("Altos_Widgets_charts_widget");
				/**/
				include_once dirname (__FILE__) . "/altos-regional-charts.php";
				register_widget ("Altos_Widgets_regional_charts_widget");
			}
		/*
		These deal with menu pages.
		*/
		function on_admin_menu ()
			{
				add_filter ("plugin_action_links", array (&$this, "on_plugin_action_links"), 10, 2);
				add_menu_page ("Altos Widgets", "Altos Widgets", "edit_plugins", "altos-widgets-options", array (&$this, "on_options_page"));
				add_submenu_page ("altos-widgets-options", "General Options", "General Options", "edit_plugins", "altos-widgets-options", array (&$this, "on_options_page"));
			}
		/**/
		function on_admin_notices ()
			{
				global $pagenow;
				/**/
				if ($pagenow && $pagenow === "plugins.php")
					{
						$options = $this->get_backward_compatible_options ();
						/**/
						if (!$this->validate_pai ($options["pai"]))
							$this->display_admin_notice ('<strong>Altos Widgets:</strong> please <a href="admin.php?page=altos-widgets-options">configure Altos Widgets</a> by supplying your Username/Password for authentication.', true);
						/**/
						if (!ini_get ("allow_url_fopen") && !function_exists ("curl_init"))
							$this->display_admin_notice ('<strong>Altos Widgets:</strong> your server is NOT yet compatible with Altos Widgets. Please set <code><a href="http://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen" target="_blank">allow_url_fopen = yes</a></code> in your <code>php.ini</code> file. If that is not possible, Altos Widgets can also use the <a href="http://php.net/manual/en/book.curl.php" target="_blank">cURL</a> extension for PHP, if your hosting provider installs it. Please contact your hosting provider to resolve this problem. <em><strong>*Tip*</strong> all of the <a href="http://wordpress.org/hosting/" target="_blank">hosting providers recommended by WordPress®</a>, support one of these two methods; by default.</em>', true);
						/**/
						if (get_option ("altos_widgets_options") && version_compare (get_option ("altos_widgets_upgrade_notice"), "1.2.4", "<")):
							$this->display_admin_notice ('<strong>Altos Widgets:</strong> Now that you\'ve upgraded, you\'ll need to re-insert any existing widgets that you\'ve been using. In other words, after upgrading, all existing Altos Widgets that you currently have in your Sidebar, will automatically disappear, and you\'ll need to re-insert updated versions of each widget.');
							update_option ("altos_widgets_upgrade_notice", "1.2.4");
						endif;
					}
			}
		/**/
		function on_plugin_action_links ($links = array (), $file = "")
			{
				if (preg_match ("/" . preg_quote ($file, "/") . "$/", __FILE__) && is_array ($links))
					{
						$settings = '<a href="admin.php?page=altos-widgets-options">Settings</a>';
						array_unshift ($links, $settings);
					}
				/**/
				return $links;
			}
		/**/
		function on_options_page () /* Handles General Options. */
			{
				$options = $this->update_all_options ();
				include_once dirname (__FILE__) . "/altos-options.php";
			}
		/**/
		function update_all_options () /* Updates all options. */
			{
				$options = $this->get_backward_compatible_options ();
				/**/
				if ($_POST["altos_widgets_options_save"])
					{
						$_POST = stripslashes_deep ($_POST);
						/**/
						foreach ($_POST as $key => $value)
							{
								if ($key !== "altos_widgets_options_save")
									if (preg_match ("/^" . preg_quote ("altos_widgets_", "/") . "/", $key))
										{
											(is_array ($value)) ? array_shift ($value) : null;
											$options[preg_replace ("/^" . preg_quote ("altos_widgets_", "/") . "/", "", $key)] = $value;
										}
							}
						/**/
						$options["pai"] = strip_tags (stripslashes ($this->auth ($options["username"], $options["password"])));
						/**/
						update_option ("altos_widgets_options", $options);
						update_option ("altos_global_options", array ("username" => $options["username"], "password" => $options["password"], "pai" => $options["pai"]));
						/**/
						if (!$this->validate_pai ($options["pai"])) /* Validate the newly obtained pai value. */
							{
								$this->display_admin_notice ('<strong>Invalid login credentials, please try again.</strong>', true);
							}
						else /* Otherwise, everything looks good! */
							$this->display_admin_notice ('<strong>Options saved.</strong>');
					}
				/**/
				return $options;
			}
		/*
		Acquires options w/backward compatiblity.
		*/
		function get_backward_compatible_options ()
			{
				$options = get_option ("altos_widgets_options");
				$options = (!is_array ($options) || empty ($options)) ? get_option ("altos_global_options") : $options;
				$options = (!is_array ($options) || empty ($options)) ? get_option ("widget_altos") : $options;
				$options = (!is_array ($options) || empty ($options)) ? array (): $options;
				/**/
				return $options;
			}
		/*
		Displays admin notifications.
		*/
		function display_admin_notice ($notice = FALSE, $error = FALSE)
			{
				if ($notice && $error) /* Special format for errors. */
					{
						echo '<div class="error fade"><p>' . $notice . '</p></div>';
					}
				else if ($notice) /* Otherwise, we just send it as an update notice. */
					{
						echo '<div class="updated fade"><p>' . $notice . '</p></div>';
					}
			}
		/*
		These apply to all widget models.
		*/
		function login_form ()
			{
				$options = $this->get_backward_compatible_options ();
				/**/
				if (isset ($_POST["altos-submit"]))
					{
						$prefix = $_POST["altos-prefix"];
						$options["username"] = strip_tags (stripslashes ($_POST[$prefix . "-altos-username"]));
						$options["password"] = strip_tags (stripslashes ($_POST[$prefix . "-altos-password"]));
						$options["pai"] = strip_tags (stripslashes ($this->auth ($options["username"], $options["password"])));
						/**/
						update_option ("altos_widgets_options", $options);
						update_option ("altos_global_options", array ("username" => $options["username"], "password" => $options["password"], "pai" => $options["pai"]));
					}
				/**/
				$username = attribute_escape ($options["username"]);
				$password = attribute_escape ($options["password"]);
				$pai = attribute_escape ($options["pai"]);
				$altos_prefix = substr (hash ("md5", mt_rand ()), 0, 10);
				/**/
				if ($this->validate_pai ($pai))
					{
						echo '<p>';
						echo '<label style="padding:6px">';
						echo '<img width="20" src="' . WP_PLUGIN_URL . '/' . basename (dirname (__FILE__)) . '/images/accepted_48.png" />';
						echo 'Your account is active.';
						echo '</label>';
						echo '</p>';
					}
				else
					{
						echo '<p>';
						echo '<label style="padding:0px; font-size: 9px">';
						echo '<img width="15" src="' . WP_PLUGIN_URL . '/' . basename (dirname (__FILE__)) . '/images/cancel_48.png" />';
						echo strlen ($pai) ? $pai : 'Please enter your Altos Research credentials.';
						echo '</label>';
						echo '</p>';
					}
				/**/
				echo '<p>';
				echo '<label>';
				echo 'Altos Research Username:<br />';
				echo '<input class="widefat" name="' . $altos_prefix . '-altos-username" type="text" value="' . $username . '" />';
				echo '</label>';
				echo '</p>';
				/**/
				echo '<p>';
				echo '<label>';
				echo 'Altos Research Password:<br />';
				echo '<input class="widefat" name="' . $altos_prefix . '-altos-password" type="password" value="' . $password . '" />';
				echo '</label>';
				echo '</p>';
				/**/
				echo '<input type="hidden" name="altos-submit" value="1" />';
				echo '<input type="hidden" name="altos-prefix" value="' . $altos_prefix . '" />';
			}
		/**/
		function has_error ($response)
			{
				if ($response->responseCode == 200)
					{
						return false;
					}
				else
					{
						return true;
					}
			}
		/**/
		function print_error_message ($response)
			{
				echo '<p>';
				echo '<label style="padding:0px; font-size: 9px">';
				echo '<img width="15" src="' . WP_PLUGIN_URL . '/' . basename (dirname (__FILE__)) . '/images/cancel_48.png" />';
				echo $response->errorMessage;
				echo '</label>';
				echo '</p>';
			}
		/**/
		function on_wp_print_scripts () /* Fired in the admin panels also. */
			{
				if (!is_admin ()) /* These scripts are only queued on the front-side. */
					{
						wp_enqueue_script ("jquery");
						wp_enqueue_script ("altos-widgets", $this->dir_url . "/altos-widgets.js", array ("jquery"), "1.2.3");
					}
			}
		/**/
		function on_wp_head ()
			{
				echo '<style type="text/css">';
				echo '.stat_table { font-size: 10px !important; font-style: normal; border: 1px solid; padding: 0px; margin: 0px; min-width: 100px; }';
				echo '.stat_table tr { padding-top: 3px; padding-left: 5px; }';
				echo '.stat_table_wide tr > td + td { padding: 0px 5px; border-right: 1px solid #333; }';
				echo '.stat_table tr > td + td + td { padding: 0px 5px; border: 0px; }';
				echo '.stat_table td { padding: 3px 5px; }';
				echo '.stat_table th { border-bottom: 1px solid #333; text-align: center; padding: 5px 5px 5px 5px; }';
				echo '.chart_style { border: 0 !important; }';
				echo '</style>';
			}
		/**/
		function print_options ($options = array (), $selected = FALSE)
			{
				foreach ($options as $key => $value)
					{
						$_selected = ($selected == $key) ? ' selected="selected"' : '';
						echo '<option value="' . $key . '"' . $_selected . '>' . $value . '</option>';
					}
			}
		/**/
		function print_checkbox_options ($name = "", $options = array (), $checked = array ())
			{
				foreach ($options as $key => $value)
					{
						$_checked = (in_array ($key, (array)$checked)) ? ' checked="checked"' : '';
						echo '<label><input type="checkbox" name="' . $name . '[]" value="' . $key . '"' . $_checked . ' /> ' . $value . '</label><br />';
					}
			}
		/**/
		function get_chart_state_city_zip_codes_array ()
			{
				$locations = null;
				/**/
				foreach ($this->get_chart_state_city_zip_codes ()->list as $location)
					{
						$locations[$location->stateName . "," . $location->cityId . "," . $location->zipId] = $location->stateName . "/" . $location->cityDisplayName . "/" . $location->zipName;
					}
				/**/
				return $locations;
			}
		/**/
		function get_regions_codes_array ()
			{
				$locations = null;
				/**/
				foreach ($this->get_regions_codes ()->list as $location)
					{
						$locations[$location->id] = $location->displayName;
					}
				/**/
				return $locations;
			}
		/**/
		function is_authenticated ()
			{
				$options = $this->get_backward_compatible_options ();
				/**/
				if ($this->validate_pai ($options["pai"]))
					{
						return true;
					}
			}
		/**/
		function validate_pai ($pai)
			{
				if (is_numeric ($pai) && $pai > 0)
					{
						return true;
					}
			}
		/**/
		function auth ($username, $password)
			{
				$success = $this->post_request (array ("service" => "auth", "username" => $username, "password" => $password));
				/**/
				if ($success->responseCode == "200")
					{
						return (int)$success->response->pai;
					}
			}
		/**/
		function get_chart_state_city_zip_codes ()
			{
				return $this->post_request (array ("service" => "listlocations", "usage" => "charts"));
			}
		/**/
		function get_regions_codes ()
			{
				return $this->post_request (array ("service" => "listregions", "usage" => "charts"));
			}
		/**/
		function get_state_city_zip_chart_url ($st, $cid, $zid, $s, $sz, $rt, $ra, $q, $ts)
			{
				return $this->post_request (array ("service" => "charturl", "st" => $st, "cid" => $cid, "zid" => $zid, "s" => $s, "sz" => $sz, "rt" => $rt, "ra" => $ra, "q" => $q, "ts" => $ts));
			}
		/**/
		function get_regional_chart_url ($zid, $s, $sz, $rt, $ra, $q, $ts)
			{
				return $this->post_request (array ("service" => "regionalcharturl", "zid" => $zid, "s" => $s, "sz" => $sz, "rt" => $rt, "ra" => $ra, "q" => $q, "ts" => $ts));
			}
		/**/
		function get_stat_table ($st, $cid, $zid, $rt, $ra, $q)
			{
				return $this->post_request (array ("service" => "tablevalues", "st" => $st, "cid" => $cid, "zid" => $zid, "rt" => $rt, "ra" => $ra, "q" => $q));
			}
		/**/
		function post_request ($parameters = null)
			{
				$options = $this->get_backward_compatible_options ();
				/**/
				$params["pai"] = $options["pai"];
				$params["rf"] = "json";
				/**/
				if (is_array ($parameters))
					{
						foreach ($parameters as $key => $param)
							{
								$params[$key] = $param;
							}
					}
				/**/
				$data = http_build_query ($params);
				/**/
				$context_options = array ("http" => array ("method" => "POST", "header" => "Content-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen ($data) . "\r\n", "content" => $data));
				/**/
				$context = stream_context_create ($context_options);
				$json = $this->fetch_url_contents ($this->webservice, false, $context);
				$response = json_decode ($json);
				/**/
				return $response;
			}
		/**/
		function fetch_url_contents ($url = "", $flags = 0, $context = NULL)
			{
				if ($url && preg_match ("/^http(s)?\:/", $url))
					{
						if (ini_get ("allow_url_fopen"))
							return@file_get_contents ($url, $flags, $context);
						/**/
						else if (function_exists ("curl_init"))
							{
								$c = (is_resource ($context)) ? stream_context_get_options ($context) : "";
								return $this->curlpsr ($url, $c["http"]["content"]);
							}
						/**/
						else /* Both disabled! */
							return false;
					}
				/**/
				return false;
			}
		/**/
		function curlpsr ($url = FALSE, $vars = FALSE)
			{
				if ($url && ($connection = @curl_init ()))
					{
						@curl_setopt ($connection, CURLOPT_URL, $url);
						@curl_setopt ($connection, CURLOPT_POST, true);
						@curl_setopt ($connection, CURLOPT_TIMEOUT, 20);
						@curl_setopt ($connection, CURLOPT_CONNECTTIMEOUT, 20);
						@curl_setopt ($connection, CURLOPT_FOLLOWLOCATION, false);
						@curl_setopt ($connection, CURLOPT_MAXREDIRS, 0);
						@curl_setopt ($connection, CURLOPT_HEADER, false);
						@curl_setopt ($connection, CURLOPT_VERBOSE, true);
						@curl_setopt ($connection, CURLOPT_ENCODING, "");
						@curl_setopt ($connection, CURLOPT_SSL_VERIFYPEER, false);
						@curl_setopt ($connection, CURLOPT_RETURNTRANSFER, true);
						@curl_setopt ($connection, CURLOPT_FORBID_REUSE, true);
						@curl_setopt ($connection, CURLOPT_FAILONERROR, true);
						@curl_setopt ($connection, CURLOPT_POSTFIELDS, $vars);
						/**/
						$output = trim (@curl_exec ($connection));
						/**/
						@curl_close ($connection);
					}
				/**/
				return (strlen ($output)) ? $output : false;
			}
	}
/*
New instance of Altos Widgets.
*/
$Altos_Widgets = new Altos_Widgets ();
?>