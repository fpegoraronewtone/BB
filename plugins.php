<?php

/**
 * BB - BlackBeard Core Plugin
 * 
 * plugins auto loading layer
 * ============================
 * 
 * BB works as plugins loader utility.
 * BB scan your plugin folders to include all available plugins into the app.
 * If a plugin exists into plugin's repositories it will be included.
 * 
 * 
 * 
 */
/**
 * Scan plugins repositories and compose a list of plugins
 * to be loaded into the app.
 * 
 * This operation is cached to improve application performance!
 * 
 */
// @TODO: this piece of logic should be cached when debug off!
#@$plugins = read_from_cache_layer();

if (empty($plugins)) {
	foreach (App::path('plugins') as $path) {
		$repo = new Folder($path);
		foreach ($repo->read()[0] as $pluginName) {

			// skip plugins that are already loaded 
			if (CakePlugin::loaded($pluginName)) {
				continue;
			}

			$plugin = array(
				'name' => $pluginName,
				'base' => $path,
				'path' => $path . $pluginName . DS,
				'order' => 5000,
				'config' => array(
					'bootstrap' => false,
					'routes' => false
				)
			);

			if (file_exists($plugin['path'] . 'Config/bootstrap.php')) {
				$plugin['config']['bootstrap'] = true;
			}

			if (file_exists($plugin['path'] . 'Config/routes.php')) {
				$plugin['config']['routes'] = true;
			}

			if (file_exists($plugin['path'] . 'bb_order')) {
				$plugin['order'] = file_get_contents($plugin['path'] . 'bb_order');
			}


			$plugins[] = $plugin;
		}
	}

	// Apply sort based on configured flag
	$plugins = sortByKey($plugins, 'order');

	// @TODO: write to a cache layer
	#write_to_cache_layer($plugins);
}








/**
 * Load Configured Plugins
 */
foreach ($plugins as $plugin) {
	CakePlugin::load($plugin['name'], $plugin['config']);
	BB::extendKey('plugin', array($plugin['name'] => $plugin));
}
