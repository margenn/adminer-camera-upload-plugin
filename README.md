## adminer-camera-upload-plugin

A plugin that allows you to take pictures and upload the images from adminer.
It was base on file-upload plugin.

Works with all fields ending with "_photo"

It depends on webcam.min.js. You can find here: https://github.com/jhuckaby/webcamjs

Your site **must** be **https** to get this plugin working.


Example of a table for testing purposes: 
``` sql
    CREATE TABLE test (
	    id int NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
	    my_photo varchar(255) NOT NULL DEFAULT '',
	    PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
```


The path you your server will look like this:
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


The index.php can look like this:
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



