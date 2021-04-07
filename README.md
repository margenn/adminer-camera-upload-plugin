## adminer-camera-upload-plugin

A plugin that allows you to take pictures and upload the images from adminer.
It was base on file-upload plugin.

Works with all fields ending with "_photo" or any other regex.

It depends on webcam.min.js. You can find here: https://github.com/jhuckaby/webcamjs

Your site **must** be **httpS** to get this plugin working.


Example of a table for testing purposes: 
``` sql
    CREATE TABLE test (
	    id int NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
	    my_photo varchar(255) NOT NULL DEFAULT '',
	    PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
```


The path of your server will look like this:
```
webserver
│   index.php
│   adminer.css
│   adminer-N.N.N-mysql-lang.php
│
└───plugins
│   │   plugin.php
│   │   camera-upload.php
│   │   webcam.min.js
│
└───uploads
    │
    └───test
        │   my_photo_210402_134704.jpg
        │   my_photo_210402_144601.jpg
        │   my_photo_210402_154832.jpg
        │   ...
```


Example of index.php: 

``` php
<?php
function adminer_object() {
	$pluginsfolder = (basename(__FILE__) == 'index.php') ? '.' : '..';
	// required to run any plugin
	include_once "$pluginsfolder/plugins/plugin.php";
	// autoloader
	foreach (glob("$pluginsfolder/plugins/*.php") as $filename) { include_once "$filename"; }
	$plugins = array(
		// specify enabled plugins here.
		new AdminerCameraUpload('./uploads/', null, array("$pluginsfolder/plugins/webcam.min.js"), '_(ph|f)oto'),
	);
	return new AdminerPlugin($plugins);
}
// calls the compiled adminer. change by yours
$arqAdminer = (basename(__FILE__) == 'index.php') ? 'adminer-4.7.9-mysql-pt-br.php' : 'index.php';
include "./$arqAdminer";
```
Note: On the code above, $pluginsfolder and $arqAdminer depends on the name of the file being executed. So, you can have the same file in production (index.php) and development (plugin.php).
