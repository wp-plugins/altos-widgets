<?php
/*
Copyright: © 2010 AltosResearch.com ( coded in the USA )
<mailto:support@altosresearch.com> <http://www.altosresearch.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License.
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
Direct access denial.
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit;
/*
Options page.
*/
echo '<div class="wrap">' . "\n";
/**/
echo '<div id="icon-plugins" class="icon32"><br /></div>' . "\n";
echo '<a href="http://www.altosresearch.com/" target="_blank"><img src="' . WP_PLUGIN_URL . '/' . basename (dirname (__FILE__)) . '/images/logo.png" style="float:right;" /></a>';
echo '<h2>Altos Widgets / General Options</h2>' . "\n";
/**/
echo '<hr />' . "\n";
/**/
echo '<form method="post">' . "\n";
echo '<input type="hidden" name="altos_widgets_options_save" value="1" />' . "\n";
/**/
echo '<h3>Altos Research Login Credentials</h3>' . "\n";
echo '<p>Please supply your Altos Research Username &amp; Password.</p>' . "\n";
/**/
echo '<table class="form-table">' . "\n";
echo '<tbody>' . "\n";
echo '<tr>' . "\n";
/**/
echo '<th>' . "\n";
echo '<label>' . "\n";
echo 'Username:' . "\n";
echo '</label>' . "\n";
echo '</th>' . "\n";
/**/
echo '</tr>' . "\n";
echo '<tr>' . "\n";
/**/
echo '<td>' . "\n";
echo '<input type="text" name="altos_widgets_username" value="' . format_to_edit ($options["username"]) . '" />' . "\n";
echo '</td>' . "\n";
/**/
echo '</tr>' . "\n";
echo '<tr>' . "\n";
/**/
echo '<th>' . "\n";
echo '<label>' . "\n";
echo 'Password:' . "\n";
echo '</label>' . "\n";
echo '</th>' . "\n";
/**/
echo '</tr>' . "\n";
echo '<tr>' . "\n";
/**/
echo '<td>' . "\n";
echo '<input type="password" name="altos_widgets_password" value="' . format_to_edit ($options["password"]) . '" />' . "\n";
echo '</td>' . "\n";
/**/
echo '</tr>' . "\n";
echo '</tbody>' . "\n";
echo '</table>' . "\n";
/**/
echo '<br />' . "\n";
/**/
echo '<p class="submit"><input type="submit" class="button-primary" value="Save Changes" /></p>' . "\n";
/**/
echo '</form>' . "\n";
/**/
echo '</div>' . "\n";
?>