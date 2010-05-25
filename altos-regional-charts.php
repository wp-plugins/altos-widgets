<?php
/*
Direct access denial.
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit;
/*
The extended WP_Widget class that handles several things.
*/
class Altos_Widgets_regional_charts_widget /* < Register this widget class. */
	extends WP_Widget /* See: /wp-includes/widgets.php for further details. */
	{
		/*
		Constructor function.
		*/
		function Altos_Widgets_regional_charts_widget () /* Builds the classname, id_base, description, etc. */
			{
				$widget_ops = array ("classname" => "altos-widgets-regional-charts-widget", "description" => "Altos Regional Charts");
				$control_ops = array ("width" => 300, "id_base" => "altos_widgets_regional_charts_widget");
				$this->WP_Widget ($control_ops["id_base"], "Altos Regional Charts", $widget_ops, $control_ops);
				/**/
				return;
			}
		/*
		Widget display function. This is where the widget actually does something.
		*/
		function widget ($args = FALSE, $instance = FALSE)
			{
				extract ($args);
				/**/
				global $Altos_Widgets;
				/**/
				$widget_options = (is_array ($instance) && !empty ($instance)) ? $instance : array();
				/**/
				if ($Altos_Widgets->is_authenticated () && is_array ($widget_options["charturls"]))
					{
						echo $before_widget . $before_title . $widget_options["title"] . $after_title;
						/**/
						echo ($widget_options["url"]) ? '<a href="' . $widget_options["url"] . '">' : '';
						/**/
						echo '<img class="chart_style altos-charts-slide-show" src="' . $widget_options["charturls"][0] . '" data-s="' . implode ("|", $widget_options["charturls"]) . '" />';
						/**/
						echo ($widget_options["url"]) ? '</a>' : '';
						/**/
						echo $after_widget;
					}
			}
		/*
		Widget form control function. This is where options are made configurable.
		*/
		function form ($instance = FALSE)
			{
				global $Altos_Widgets;
				/**/
				$widget_options = (is_array ($instance) && !empty ($instance)) ? $instance : array();
				/**/
				if ($Altos_Widgets->is_authenticated ())
					{
						echo '<p>';
						echo '<label>';
						echo 'Widget Title:<br />';
						echo '<input class="widefat" id = "' . $this->get_field_id ("title") . '" name="' . $this->get_field_name ("title") . '" type="text" value="' . $widget_options["title"] . '" />';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>Select One Or More Locations:</label> <a href="#" onclick="alert(\'Selecting multiple locations creates a slide-show.\'); return false;" title="Selecting multiple locations creates a slide-show.">[?]</a>';
						echo '<div style="margin: 1px; padding: 5px; height: 90px; overflow-y: scroll; overflow-x: visible; background: #FFFFFF; border: 1px solid #CCCCCC; -moz-border-radius: 4px; -webkit-border-radius: 4px; border-radius: 4px;">';
						echo '', $Altos_Widgets->print_checkbox_options ($this->get_field_name ("zid"), $Altos_Widgets->get_regions_codes_array (), $widget_options["zid"]);
						echo '</div><br />';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Statistic:<br />';
						echo '<select name="' . $this->get_field_name ("s") . '">', $Altos_Widgets->print_options ($Altos_Widgets->s[1], $widget_options["s"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Chart Size:<br />';
						echo '<select name="' . $this->get_field_name ("sz") . '">', $Altos_Widgets->print_options ($Altos_Widgets->sz, $widget_options["sz"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Residence Type:<br />';
						echo '<select name="' . $this->get_field_name ("rt") . '">', $Altos_Widgets->print_options ($Altos_Widgets->rt[1], $widget_options["rt"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Rolling Average:<br />';
						echo '<select name="' . $this->get_field_name ("ra") . '">', $Altos_Widgets->print_options ($Altos_Widgets->ra, $widget_options["ra"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Quartile:<br />';
						echo '<select name="' . $this->get_field_name ("q") . '">', $Altos_Widgets->print_options ($Altos_Widgets->q, $widget_options["q"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Time Span:<br />';
						echo '<select name="' . $this->get_field_name ("ts") . '">', $Altos_Widgets->print_options ($Altos_Widgets->ts, $widget_options["ts"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Link Chart to URL:<br />';
						echo '<input name="' . $this->get_field_name ("url") . '" type="text" title="Remember to include the http://" value="' . $widget_options["url"] . '" />';
						echo '</label>';
						echo '</p>';
					}
				else
					{
						$Altos_Widgets->login_form ();
					}
			}
		/*
		Widget update function. This is where an updated instance is configured/stored.
		*/
		function update ($instance = FALSE, $old = FALSE)
			{
				global $Altos_Widgets;
				/**/
				$widget_options = stripslashes_deep ($instance);
				/**/
				$widget_options["charturls"] = array (); /* Reset array. */
				/**/
				foreach ((array)$widget_options["zid"] as $zid)
					{
						$charturl = $Altos_Widgets->get_regional_chart_url ($zid, $widget_options["s"], $widget_options["sz"], $widget_options["rt"], $widget_options["ra"], $widget_options["q"], $widget_options["ts"]);
						/**/
						if ($Altos_Widgets->has_error ($charturl))
							{
								$Altos_Widgets->print_error_message ($charturl);
								return $old; /* Revert to previous options. */
							}
						/**/
						$widget_options["charturls"][] = $charturl->response->url;
					}
				/**/
				return $widget_options;
			}
	}
?>