<?php

	class renderer
	{
		function wa_header()
		{
			page(_("Main Menu"), false, true);
		}

		function wa_footer()
		{
			end_page(false, true);
		}

		function menu_header($title, $no_menu, $is_index)
		{
			global $path_to_root, $applications, $help_base_url, $db_connections, $app_title, $version;
			// you can owerride the table styles from config.php here, if you want.
			//global $table_style, $table_style2;
			//$table_style  = "cellpadding=3 border=1 bgcolor='#f9f9f9' bordercolor='#8cacbb' style='border-collapse: collapse'";
			//$table_style2 = "cellpadding=3 border=1 bgcolor='#f9f9f9' bordercolor='#cccccc' style='border-collapse: collapse'";
			$sel_app = $_SESSION['sel_app'];
			echo "<div id='content'>\n";
			if (!$no_menu)
			{
				echo "<div id='top'>\n";
				echo "<p>" . $db_connections[$_SESSION["wa_current_user"]->company]["name"] . " | " . $_SERVER['SERVER_NAME'] . " | " . $_SESSION["wa_current_user"]->name . "</p>\n";
				echo "<ul>\n";
				echo "  <li><a href='$path_to_root/admin/display_prefs.php?'>" . _("Preferences") . "</a></li>\n";
				echo "  <li><a href='$path_to_root/admin/change_current_user_password.php?selected_id=" . $_SESSION["wa_current_user"]->username . "'>" . _("Change password") . "</a></li>\n";
			 	if ($help_base_url != null)
					echo "  <li><a target = '_blank' onclick=" .'"'."javascript:openWindow(this.href,this.target); return false;".'" '. "href='". help_url($title, $sel_app)."'>" . _("Help") . "</a></li>";
				echo "  <li><a href='$path_to_root/access/logout.php?'>" . _("Logout") . "</a></li>";
				echo "</ul>\n";
				echo "</div>\n";
				echo "<div id='logo'>\n";
				echo "<h1>$app_title $version</h1>\n";
				echo "<p id='slogan'>" . _("Theme:") . " " . user_theme() . "</p>\n";
				echo "</div>\n";
				$local_path_to_root = $path_to_root;

				echo "<ul id='tabs'>\n";
				foreach($applications as $app => $name)
				{
					echo "<li><a ".($sel_app == $app ? "class='current' " : "") .
						"href='$local_path_to_root/index.php?application=" . $app .
						SID . "'>" . $name . "</a></li>\n";
				}
				echo "</ul>\n";
			}
			echo "<div id='wrapper'>\n";
			if ($title && !$no_menu && !$is_index)
			{
				echo "<center><table width='100%'><tr><td width='100%' class='titletext'>$title</td>"
				."<td align=right>"
				.(user_hints() ? "<span id='hints'></span>" : '')
				."</td>"
				."</tr></table></center>";
			}
			if (!$is_index)
				echo "<br>";

		}

		function menu_footer($no_menu, $is_index)
		{
			global $path_to_root, $power_url, $power_by;
			include_once($path_to_root . "/includes/date_functions.inc");

			if ($no_menu == false)
			{
				echo "<div id='footer'>\n";
				if (isset($_SESSION['wa_current_user']))
				{
					echo "<span class='power'><a target='_blank' href='$power_url'>$power_by</a></span\n";
					echo "<span class='date'>".Today() . " | " . Now()."</span>\n";
				}
				echo "</div>\n";
			}
			echo "</div>\n";
			echo "</div>\n";
		}

		function display_applications(&$waapp)
		{

			$selected_app = $waapp->get_selected_application();

			foreach ($selected_app->modules as $module)
			{
				// image
				echo "<table width='100%'><tr>";
				echo "<td valign='top' class='menu_group'>";
				echo "<table border=0 width='100%'>";
				echo "<tr><td class='menu_group'>";
				echo $module->name;
				echo "</td></tr><tr>";
				echo "<td width='50%' class='menu_group_items'>";
				echo "<ul>\n";
				if ($_SESSION["language"]->dir == "rtl")
					$class = "right";
				else
					$class = "left";
				foreach ($module->lappfunctions as $appfunction)
				{
					if ($appfunction->label == "")
						echo "<li class='empty'>&nbsp;</li>\n";
					elseif ($_SESSION["wa_current_user"]->can_access_page($appfunction->access))
						echo "<li><a class='$class' href='$appfunction->link'> " . $appfunction->label . "</a></li>\n";
				}
				echo "</ul></td>\n";
				if (sizeof($module->rappfunctions) > 0)
				{
					echo "<td width='50%' class='menu_group_items'>";
					echo "<ul>\n";
					foreach ($module->rappfunctions as $appfunction)
					{
						if ($appfunction->label == "")
							echo "<li class='empty'>&nbsp;</li>\n";
						elseif ($_SESSION["wa_current_user"]->can_access_page($appfunction->access))
							echo "<li><a class='$class' href='$appfunction->link'> " . $appfunction->label . "</a></li>\n";
					}
					echo "</ul></td>\n";
				}
				echo "</tr></table></td></tr></table>\n";
			}
		}
	}

?>