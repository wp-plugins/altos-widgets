<?php
/*
Direct access denial.
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit;
/*
The extended WP_Widget class that handles several things.
*/
class Altos_Widgets_stat_table_widget /* < Register this widget class. */
	extends WP_Widget /* See: /wp-includes/widgets.php for further details. */
	{
		/*
		Constructor function.
		*/
		function Altos_Widgets_stat_table_widget () /* Builds the classname, id_base, description, etc. */
			{
				$widget_ops = array ("classname" => "altos-widgets-stat-table-widget", "description" => "Altos Stat Table");
				$control_ops = array ("width" => 300, "id_base" => "altos_widgets_stat_table_widget");
				$this->WP_Widget ($control_ops["id_base"], "Altos Stat Table", $widget_ops, $control_ops);
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
				if ($Altos_Widgets->is_authenticated () && isset ($widget_options["table"]))
					{
						$widget_options_table = $Altos_Widgets->get_stat_table($widget_options["st"], $widget_options["cid"], $widget_options["zid"], $widget_options["rt"], $widget_options["ra"], $widget_options["q"])->response;

						/**/
						echo $before_widget . $before_title . $widget_options["title"] . $after_title;
						/**/
						if ($widget_options["l"] == "f")
							{
								echo '<table border="0" padding="0" cellspacing="0" class="stat_table stat_table_wide">';
								/**/
								echo '<tr>';
								echo '<th colspan="4">';
								echo ($widget_options["url"]) ? '<a href="' . $widget_options["url"] . '">' : '';
								echo $widget_options_table->rollingAverage . ' stats for ' . $widget_options_table->residenceType . ' properties in<br />' .
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
						echo '<label>';
						echo 'Select Location:<br />';
						echo '<select name="' . $this->get_field_name ("st,cid,zid") . '">', $Altos_Widgets->print_options ($Altos_Widgets->get_chart_state_city_zip_codes_array (), $widget_options["st,cid,zid"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Table Format:<br />';
						echo '<select name="' . $this->get_field_name ("l") . '">', $Altos_Widgets->print_options ($Altos_Widgets->l, $widget_options["l"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Residence Type:<br />';
						echo '<select name="' . $this->get_field_name ("rt") . '">', $Altos_Widgets->print_options ($Altos_Widgets->rt[0], $widget_options["rt"]), '</select>';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<p>';
						echo '<label>';
						echo 'Link Table to URL:<br />';
						echo '<input name="' . $this->get_field_name ("url") . '" type="text" title="Remember to include the http://" value="' . $widget_options["url"] . '" />';
						echo '</label>';
						echo '</p>';
						/**/
						echo '<input type="hidden" name="' . $this->get_field_name ("q") . '" value="a" />';
						echo '<input type="hidden" name="' . $this->get_field_name ("ra") . '" value="a" />';
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
				list ($widget_options["st"], $widget_options["cid"], $widget_options["zid"]) = explode (",", $widget_options["st,cid,zid"]);
				/**/
				$table = $Altos_Widgets->get_stat_table ($widget_options["st"], $widget_options["cid"], $widget_options["zid"], $widget_options["rt"], $widget_options["ra"], $widget_options["q"]);
				/**/
				if ($Altos_Widgets->has_error ($table))
					{
						$Altos_Widgets->print_error_message ($table);
						return $old; /* Revert to previous options. */
					}
				/**/
				$widget_options["table"] = $table;
				return $widget_options;
			}
	}
?>
