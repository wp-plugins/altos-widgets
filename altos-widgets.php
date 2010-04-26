<?php
/*
Copyright: © 2010 AltosResearch.com ( coded in the USA )
<mailto:support@altosresearch.com> <http://www.altosresearch.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License.
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
Version: 1.2.1
Stable tag: trunk
Tested up to: 2.9.2
Requires at least: 2.7
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
		var $s, $sz, $rt, $ra, $q, $ts, $l;
		/**/
		var $webservice = "https://www.altosresearch.com/altos/app";
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
				add_action ("widgets_init", array (&$this, "on_widget_init"));
				add_action ("wp_head", array (&$this, "on_wp_head"));
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
				$this->ts = array ("e" => "1-year", "z" => "All Available Data");
				/**/
				$this->l = array ("b" => "Narrow", "f" => "Wide");
			}
		/*
		Widget initializer.
		*/
		function on_widget_init ()
			{
				register_sidebar_widget ("Altos Stat Table", array (&$this, "on_altos_stat_table_register"));
				register_widget_control ("Altos Stat Table", array (&$this, "on_altos_stat_table_control"));
				/**/
				register_sidebar_widget ("Altos Charts", array (&$this, "on_altos_charts_register"));
				register_widget_control ("Altos Charts", array (&$this, "on_altos_charts_control"));
				/**/
				register_sidebar_widget ("Altos Regional Charts", array (&$this, "on_altos_regional_charts_register"));
				register_widget_control ("Altos Regional Charts", array (&$this, "on_altos_regional_charts_control"));
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
		These acquire options w/backward compatiblity.
		*/
		function get_backward_compatible_widget_options ($widget = FALSE)
			{
				$widget_options = get_option ("altos_widgets_" . $widget . "_options");
				$BackCompatible = "Altos" . preg_replace ("/ /", "", ucwords (preg_replace ("/_/", " ", $widget)));
				$widget_options = (!is_array ($widget_options) || empty ($widget_options)) ? get_option ($BackCompatible) : $widget_options;
				$widget_options = (!is_array ($widget_options) || empty ($widget_options)) ? get_option ("widget_altos") : $widget_options;
				$widget_options = (!is_array ($widget_options) || empty ($widget_options)) ? array (): $widget_options;
				/**/
				return $widget_options;
			}
		/**/
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
		Stat Table widget output.
		*/
		function on_altos_stat_table_register ($args)
			{
				extract ($args);
				/**/
				$widget_options = $this->get_backward_compatible_widget_options ("stat_table");
				/**/
				if ($this->is_authenticated () && isset ($widget_options["table"]))
					{
						$widget_options_table = $widget_options["table"]->response;
						/**/
						echo $before_widget . $before_title . $widget_options["altos-stat-table-title"] . $after_title;
						/**/
						if ($widget_options["l"] == "f")
							{
								echo '<table border="0" padding="0" cellspacing="0" class="stat_table stat_table_wide">';
								/**/
								echo '<tr>';
								echo '<th colspan="4">';
								echo ($widget_options["url"]) ? '<a href="' . $widget_options["url"] . '">' : '';
								echo $widget_options_table->rollingAverage . ' stats for ' . $widget_options_table->residenceType . ' properties in<br />';
								$widget_options_table->cityName . ', ' . $widget_options_table->state . ' ' . $widget_options_table->zipCode . ' as of ' . $widget_options_table->date;
								echo ($widget_options["url"]) ? '</a>' : '';
								echo '</th>';
								echo '</tr>';
								/**/
								echo '<tr class="odd">';
								echo '<td>Median List Price</td><td>' . $widget_options_table->medianPrice . '</td>';
								echo '<td>Average List Price</td><td>' . $widget_options_table->meanPrice . '</td>';
								echo '</tr>';
								/**/
								echo '<tr class="even">';
								echo '<td>Total Inventory</td><td>' . $widget_options_table->inventory . '</td>';
								echo '<td>Price per Square Foot</td><td>' . $widget_options_table->pricePerSquareFoot . '</td>';
								echo '</tr>';
								/**/
								echo '<tr class="odd">';
								echo '<td>Average Home Size</td><td>' . $widget_options_table->medianSquareFoot . '</td>';
								echo '<td>Median Lot Size</td><td>' . $widget_options_table->medianLotSize . '</td>';
								echo '</tr>';
								/**/
								echo '<tr class="even">';
								echo '<td>Average # Beds</td><td>' . $widget_options_table->meanBeds . '</td>';
								echo '<td>Average # Baths</td><td>' . $widget_options_table->meanBaths . '</td>';
								echo '</tr>';
								/**/
								echo '<tr class="odd">';
								echo '<td>Homes Absorbed</td><td>' . $widget_options_table->medianAbsorbed . '</td>';
								echo '<td>Newly Listed</td><td>' . $widget_options_table->newlyListed . '</td>';
								echo '</tr>';
								/**/
								echo '<tr class="even">';
								echo '<td>Days on Market</td><td>' . $widget_options_table->daysOnMarket . '</td>';
								echo '<td>Average Age</td><td>' . $widget_options_table->meanAge . '</td>';
								echo '</tr>';
								/**/
								echo '</table>';
							}
						else if ($widget_options["l"] == "b")
							{
								echo '<table border="0" padding="0" cellspacing="0" class="stat_table stat_table_narrow">';
								/**/
								echo '<tr>';
								echo '<th colspan="2">';
								echo ($widget_options["url"]) ? '<a href="' . $widget_options["url"] . '">' : '';
								echo $widget_options_table->cityName . ', ' . $widget_options_table->state . ' ' . $widget_options_table->zipCode . '<br />';
								echo $widget_options_table->residenceType . '<br />';
								echo $widget_options_table->date;
								echo ($widget_options["url"]) ? '</a>' : '';
								echo '</th>';
								echo '</tr>';
								/**/
								echo '<tr class="odd">';
								echo '<td>Median List Price</td><td>' . $widget_options_table->medianPrice . '</td>';
								echo '</tr>';
								/**/
								echo '<tr class="even">';
								echo '<td>Total Inventory</td><td>' . $widget_options_table->inventory . '</td>';
								echo '</tr>';
								/**/
								echo '<tr class="odd">';
								echo '<td>Homes Absorbed</td><td>' . $widget_options_table->medianAbsorbed . '</td>';
								echo '</tr>';
								/**/
								echo '<tr class="even">';
								echo '<td>Days on Market</td><td>' . $widget_options_table->daysOnMarket . '</td>';
								echo '</tr>';
								/**/
								echo '</table>';
							}
						/**/
						echo $after_widget;
					}
			}
		/*
		Stat Table widget control.
		*/
		function on_altos_stat_table_control ()
			{
				if ($this->is_authenticated ())
					{
						$widget_options = $this->get_backward_compatible_widget_options ("stat_table");
						/**/
						if (isset ($_POST["altos-stat-table-control-submit"]))
							{
								unset ($_POST["altos-stat-table-control-submit"]);
								$widget_options = stripslashes_deep ($_POST);
								/**/
								list ($widget_options["st"], $widget_options["cid"], $widget_options["zid"]) = explode (",", $widget_options["st,cid,zid"]);
								/**/
								$response = $this->get_stat_table ($widget_options["st"], $widget_options["cid"], $widget_options["zid"], $widget_options["rt"], $widget_options["ra"], $widget_options["q"]);
								/**/
								if ($this->has_error ($response))
									{
										$this->print_error_message ($response);
									}
								else
									{
										$widget_options["table"] = $response;
										update_option ("altos_widgets_stat_table_options", $widget_options);
									}
							}
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Widget Title:<br />';
						echo '<input class="widefat" name="altos-stat-table-title" type="text" value="' . $widget_options["altos-stat-table-title"] . '" />';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Select Location:<br />';
						echo '<select name="st,cid,zid">', $this->print_options ($this->get_chart_state_city_zip_codes_array (), $widget_options["st,cid,zid"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Table Format:<br />';
						echo '<select name="l">', $this->print_options ($this->l, $widget_options["l"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Residence Type:<br />';
						echo '<select name="rt">', $this->print_options ($this->rt[0], $widget_options["rt"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Link Table to URL:<br />';
						echo '<input name="url" type="text" title="Remember to include the http://" value="' . $widget_options["url"] . '" />';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<input type="hidden" name="q" value="a" />';
						echo '<input type="hidden" name="ra" value="a" />';
						echo '<input type="hidden" name="altos-stat-table-control-submit" value="1" />';
					}
				else
					{
						$this->login_form ();
					}
			}
		/*
		Chart widget output.
		*/
		function on_altos_charts_register ($args)
			{
				extract ($args);
				/**/
				$widget_options = $this->get_backward_compatible_widget_options ("charts");
				/**/
				if ($this->is_authenticated () && isset ($widget_options["charturl"]))
					{
						echo $before_widget . $before_title . $widget_options["altos-charts-title"] . $after_title;
						/**/
						echo ($widget_options["url"]) ? '<a href="' . $widget_options["url"] . '">' : '';
						/**/
						echo '<img class="chart_style" src="' . $widget_options["charturl"] . '" />';
						/**/
						echo ($widget_options["url"]) ? '</a>' : '';
						/**/
						echo $after_widget;
					}
			}
		/*
		Chart widget control.
		*/
		function on_altos_charts_control ()
			{
				if ($this->is_authenticated ())
					{
						$widget_options = $this->get_backward_compatible_widget_options ("charts");
						/**/
						if (isset ($_POST["altos-charts-control-submit"]))
							{
								unset ($_POST["altos-charts-control-submit"]);
								$widget_options = stripslashes_deep ($_POST);
								/**/
								list ($widget_options["st"], $widget_options["cid"], $widget_options["zid"]) = explode (",", $widget_options["st,cid,zid"]);
								/**/
								$response = $this->get_state_city_zip_chart_url ($widget_options["st"], $widget_options["cid"], $widget_options["zid"], $widget_options["s"], $widget_options["sz"], $widget_options["rt"], $widget_options["ra"], $widget_options["q"], $widget_options["ts"]);
								/**/
								if ($this->has_error ($response))
									{
										$this->print_error_message ($response);
									}
								else
									{
										$widget_options["charturl"] = $response->response->url;
										update_option ("altos_widgets_charts_options", $widget_options);
									}
							}
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Widget Title:<br />';
						echo '<input class="widefat" name="altos-charts-title" type="text" value="' . $widget_options["altos-charts-title"] . '" />';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Select Location:<br />';
						echo '<select name="st,cid,zid">', $this->print_options ($this->get_chart_state_city_zip_codes_array (), $widget_options["st,cid,zid"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Statistic:<br />';
						echo '<select name="s">', $this->print_options ($this->s[0], $widget_options["s"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Chart Size:<br />';
						echo '<select name="sz">', $this->print_options ($this->sz, $widget_options["sz"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Residence Type:<br />';
						echo '<select name="rt">', $this->print_options ($this->rt[1], $widget_options["rt"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Rolling Average:<br />';
						echo '<select name="ra">', $this->print_options ($this->ra, $widget_options["ra"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Quartile:<br />';
						echo '<select name="q">', $this->print_options ($this->q, $widget_options["q"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Time Span:<br />';
						echo '<select name="ts">', $this->print_options ($this->ts, $widget_options["ts"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Link Chart to URL:<br />';
						echo '<input name="url" type="text" title="Remember to include the http://" value="' . $widget_options["url"] . '" />';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<input type="hidden" name="altos-charts-control-submit" value="1" />';
					}
				else
					{
						$this->login_form ();
					}
			}
		/*
		Regional Chart widget output.
		*/
		function on_altos_regional_charts_register ($args)
			{
				extract ($args);
				/**/
				$widget_options = $this->get_backward_compatible_widget_options ("regional_charts");
				/**/
				if ($this->is_authenticated () && isset ($widget_options["charturl"]))
					{
						echo $before_widget . $before_title . $widget_options["altos-regional-charts-title"] . $after_title;
						/**/
						echo ($widget_options["url"]) ? '<a href="' . $widget_options["url"] . '">' : '';
						/**/
						echo '<img class="chart_style" src="' . $widget_options["charturl"] . '" />';
						/**/
						echo ($widget_options["url"]) ? '</a>' : '';
						/**/
						echo $after_widget;
					}
			}
		/*
		Regional Chart widget control.
		*/
		function on_altos_regional_charts_control ()
			{
				if ($this->is_authenticated ())
					{
						$widget_options = $this->get_backward_compatible_widget_options ("regional_charts");
						/**/
						if (isset ($_POST["altos-regional-charts-control-submit"]))
							{
								unset ($_POST["altos-regional-charts-control-submit"]);
								$widget_options = stripslashes_deep ($_POST);
								/**/
								$response = $this->get_regional_chart_url ($widget_options["zid"], $widget_options["s"], $widget_options["sz"], $widget_options["rt"], $widget_options["ra"], $widget_options["q"], $widget_options["ts"]);
								/**/
								if ($this->has_error ($response))
									{
										$this->print_error_message ($response);
									}
								else
									{
										$widget_options["charturl"] = $response->response->url;
										update_option ("altos_widgets_regional_charts_options", $widget_options);
									}
							}
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Widget Title:<br />';
						echo '<input class="widefat" name="altos-regional-charts-title" type="text" value="' . $widget_options["altos-regional-charts-title"] . '" />';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Select Location:<br />';
						echo '<select name="zid">', $this->print_options ($this->get_regions_codes_array (), $widget_options["zid"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Statistic:<br />';
						echo '<select name="s">', $this->print_options ($this->s[1], $widget_options["s"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Chart Size:<br />';
						echo '<select name="sz">', $this->print_options ($this->sz, $widget_options["sz"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Residence Type:<br />';
						echo '<select name="rt">', $this->print_options ($this->rt[1], $widget_options["rt"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Rolling Average:<br />';
						echo '<select name="ra">', $this->print_options ($this->ra, $widget_options["ra"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Quartile:<br />';
						echo '<select name="q">', $this->print_options ($this->q, $widget_options["q"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Time Span:<br />';
						echo '<select name="ts">', $this->print_options ($this->ts, $widget_options["ts"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Link Chart to URL:<br />';
						echo '<input name="url" type="text" title="Remember to include the http://" value="' . $widget_options["url"] . '" />';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<input type="hidden" name="altos-regional-charts-control-submit" value="1" />';
					}
				else
					{
						$this->login_form ();
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
		function on_wp_head ()
			{
				echo '<style type="text/css">';
				echo '.stat_table { font-size: 10px !important; font-style: normal; border: 1px solid; padding: 0px; margin: 0px; min-width: 100px; }';
				echo '.stat_table tr { padding-top: 3px; padding-left: 5px; }';
				echo '.stat_table_wide tr > td + td { padding: 0px 5px; border-right: 1px solid #333; }';
				echo '.stat_table tr > td + td + td { padding: 0px 5px; border: 0px; }';
				echo '.stat_table td { padding: 3px 5px; }';
				echo '.stat_table th { border-bottom: 1px solid #333; text-align: center; padding: 5px 5px 5px 5px; }';
				echo '.chart_style { padding-top: 10px; }';
				echo '</style>';
			}
		/**/
		function print_options ($options, $selected = null)
			{
				foreach ($options as $key => $value)
					{
						$_selected = ($selected == $key) ? ' selected="selected"' : '';
						echo '<option value="' . $key . '"' . $_selected . '>' . $value . '</option>';
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