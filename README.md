# AdminerCameraUpload

A plugin that allows you to take pictures and upload the images from adminer.
It was base on file-upload plugin.

Works with all fields ending with "_photo" or any other regex.

It depends on webcam.min.js. You can find here: https://github.com/jhuckaby/webcamjs

Your site **must** be **httpS** to get this plugin working.


<br>

# Using:

<br>

## index.php

Create an `index.php` with the following content:

```php
<?php
function adminer_object() {
	include_once "./plugins/plugin.php"; // required to run any plugin
	foreach (glob("./plugins/*.php") as $filename) { include_once $filename; } // autoloader
	$plugins = array(
		// specify plugins here.
		new AdminerCameraUpload()
	);
	return new AdminerPlugin($plugins);
}

include "./your-compiled-or-downloaded-adminer.php";
```

<br>

## Folder structure

A typical deploy looks like this:

```ini
📂 webserver_root
├── 📂 adminer
│   ├── 📄 adminer.css
│   ├── 📄 index.php
│   ├── 📄 your-compiled-or-downloaded-adminer.php
│   └── 📂 plugins
│       ├── 📄 plugin.php
│       ├── 📄 camera-upload.php # plugin goes here
│       └── 📂 static
│           └── 📄 webcam.min.js
├── 📂 photos # uploaded photos path (customizable)
│   └── 📂 db
│        └── 📂 table
│            └── 📂 field
│                ├── 📄 field_241208_201430.jpg
│                ├── 📄 field_241208_201535.jpg
│                ├── 📄 field_241208_201621.jpg
│                └── 📄 ...
```


<br>

## Test Table

Example of a table for testing purposes:

``` sql
    CREATE TABLE test (
	    id int NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
	    my_photo varchar(255),
	    PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
```
