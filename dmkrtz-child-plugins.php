<?php
/*
 * Plugin Name:       ! dmkrtz Child Plugins
 * GitHub Plugin URI: dmkrtz/dmkrtz-child-plugins
 * Description:       By activating this plugin you will get a fatal error message, but the output tells you what has been done. Usually it backs up existing files from the original plugin, and replaces them with the ones inside your child plugins. Click the activate button if you've done changes, or when a plugin was updated to get the modified version back.
 * Version:           0.0.3
 * Author:            dmkrtz
 * Author URI:        https://dmkrtz.de/
 */
 
 /*
	So the idea is to be able to have "child plugins" with this plugin.
	You have to build folders in this very plugin folder configured like this:
	
	Say you want to update the GiveWP plugin its Donor Wall Shortcode (that's why I made this plugin).
	So in here (plugins/dmkrtz-child-plugins/) you have to create a folder "give-child" ("give" is the plugin name, "-child" the indicator).
	
	The shortcode-donor-wall.php is located in "give/templates/shortcode-donor-wall.php".
	
	So our ultimate path will be:
	"plugins/dmkrtz-child-plugins/give-child/templates/shortcode-donor-wall.php"
	
	As of version 0.0.1, the "child version" will be applied when you activate this plugin and return a fatal error
	I will think about a different approach very soon but this seems to be basic enough to work as of now.
	
	Now we do a little hacking *insert gif*.
*/

function full_copy($source, $dest, $plugindir, $backupdir) {	
	$destarr = explode(DIRECTORY_SEPARATOR, $dest);
	
	$source = "$plugindir/$source";
	$dest = "$backupdir/$dest";
	
	// go through each part of the path
	foreach($destarr as $i) {
		// check if file or folder, because file can be copied, folder has to be created before
		if(!pathinfo($i, PATHINFO_EXTENSION)) {
			// make folders
			$p .= $i . DIRECTORY_SEPARATOR;
			// echo "Folder: $p<br>";
			
			if(file_exists("$plugindir/$p")) {
				if(!file_exists("$backupdir/$p")) {
					mkdir("$backupdir/$p", 0777);
					$result[] = "Created \"$backupdir/$p\".";
				}
			}
		} else {
			$p .= $i;
			// echo "File: $p<br>";
			
			// check if file actually exists in source
			if(file_exists($source)) {
				if(copy($source, $dest))
					$result[] = str_replace("$plugindir/", "plugins/", "Backed up \"$source\".");
			}
		}
	}
	
	return implode("<br>", $result);
}

function dmkrtz_child_plugins_activate() {
	/* activation code here */
	$dir = __DIR__;
	$backupdir = "$dir/backup";
	
	if(!file_exists($backupdir))
		mkdir($backupdir);
	
	$childs = glob("$dir/*-child", GLOB_ONLYDIR);
	
	if(!count($childs)>0) {
		// no plugins found
		return;
	}
	
	// else we continue here
	// get all child files
	foreach($childs as $p) {
		$childname = basename($p);
		$pluginname = str_replace("-child", "", $childname);
		
		// get all single files
		foreach(glob("$p/{,*/,*/*/,*/*/*/}*.*", GLOB_BRACE) as $f) {
			$filepath = str_replace([$dir . "/", $childname], "", $f);
			$childfiles[$childname][] = $filepath;
			$pluginfiles[$pluginname][] = $filepath;
		}
		
		// backup
		foreach($pluginfiles as $p => $f) {
			foreach($f as $file) {
				//$source = WP_PLUGIN_DIR . "/$p$file";
				$source = "$p$file";
				//$dest = $backupdir . "/$p$file";
				$dest = "$p$file";
				
				// $json[] = full_copy($source, $dest, WP_PLUGIN_DIR, $backupdir);
			}
		}

		// move files
		foreach($childfiles as $p => $f) {
			foreach($f as $file) {
				$childfile = $dir . "/$p" . $file;
				$pluginfile = WP_PLUGIN_DIR . "/$pluginname" . $file;

				if(copy($childfile, $pluginfile)) {
					$msg = "<span style='font-weight: bold; color: green;'>successful!</span>";
				} else {
					$msg = "<span style='font-weight: bold; color: red;'>NOT successful!</span>";
				}
				
				$json[] = "Replacing \"plugins/{$pluginname}{$file}\" with \"{$p}{$file}\" $msg";
			}
		}
	}
	
	if(count($json)>0) {
		echo "<body style='margin: 0;'><div style='font-size: 13px; font-family: -apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,Oxygen-Sans,Ubuntu,Cantarell,\"Helvetica Neue\",sans-serif;'>";
			echo "<b>But it still did something:</b></br>";
			
			foreach($json as $j) {
				echo $j . "</br>";
			}
		echo "</div></body>";
	}
	
	die(); // makes the activation fail, but return info in an error message (good)
}
register_activation_hook( __FILE__, 'dmkrtz_child_plugins_activate' );
