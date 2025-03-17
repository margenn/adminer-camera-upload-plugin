<?php

/** Edit fields ending with "_photo" with a camera upload interface and link to the uploaded photos from select
 * This plugin was based on file-upload.php
 * the connection MUST be HTTPS otherwise you get a 'No supported webcam interface found'
 * @link https://www.adminer.org/plugins/#use
 * @author Marcelo Gennari, https://gren.com.br/
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 * @version 2.0.0
 */
class AdminerCameraUpload {
	/** @access protected */
	var $uploadPath, $displayPath, $scripts, $fieldsufix;

	/**
	* @param string folder where uploaded photos will be stored
	* @param string prefix for displaying data, null stands for $uploadPath
	* @param string required js libraries
	* @param string fields terminated with this regex, will activate this plugin
	*/
	function __construct($uploadPath = "./photos", $displayPath = null
			, $scripts = array("./plugins/static/webcam.min.js"), $fieldsufix = '_photo') {
		$this->uploadPath = rtrim($uploadPath, '/');
		$this->displayPath =  $displayPath ? rtrim($displayPath, '/') : $this->uploadPath;
		$this->scripts = $scripts;
		$this->fieldsufix = '~(.*)' . $fieldsufix . '$~';
	}

	function head() {
		// INICIAL CHECK
		// interface must be 'edit'
		if (! isset($_GET['edit']) ) { return; }
		// $fields can't be null
		$fields = Adminer\fields($_GET["edit"]); if (! $fields ) { return; }
		// at least one photo field
		$hasPhotoField = false;
		foreach ($fields as $field) {
			if ( preg_match($this->fieldsufix, $field["field"]) ) {
				$hasPhotoField = true;
			}
		}
		if (! $hasPhotoField ) { return; }
		// ALL ABOVE IS OK, PROCEED

		// load js
		foreach ($this->scripts as $script) {
			echo Adminer\script_src($script);
		}
	}

	// Start (Take camera control), Snap (record snapshot), Redo (take another photo), OK (Release camera control)
	function editInput($table, $field, $attrs, $value) {
		if (preg_match($this->fieldsufix, $field["field"])) {
			$fieldname = "$field[field]";
			$return = "";
			if (!empty($value)) { // link to the attached file
				// $return .= "<a target='_blank' href='$this->displayPath/$_GET[db]/$table/$fieldname/$value'> <div id='attachedfile'>$value</div> </a>";
				$imageUrl = "$this->displayPath/$_GET[db]/$table/$fieldname/$value";
				$return .= "<a target='_blank' href='$imageUrl'> <img id='attachedimg' src='$imageUrl' alt='$value' /> </a>";
			}
			$return .= "<input type='hidden' id='$fieldname' name='$fieldname'>\n";
			$return .= "<div id='preview_$fieldname'></div>\n";
			$return .= Adminer\script("
			function start_$fieldname() {
				if (! Webcam.loaded) {
					var preview_w=200, preview_h=150, resolution_multiplier=3.2;
					var isMobile = navigator.userAgent.toLowerCase().match(/mobile/i) ? true : false;
					var mobilerearcam = isMobile ? {facingMode:'environment'} : {};
					if (isMobile && (screen.width<screen.height)) {tmp=preview_w;preview_w=preview_h;preview_h=tmp;}
					Webcam.set({ width:preview_w, height:preview_h, dest_width:resolution_multiplier*preview_w, dest_height:resolution_multiplier*preview_h, image_format:'jpeg', jpeg_quality:90, constraints:mobilerearcam }); Webcam.attach('#preview_$fieldname');
					document.getElementById('start_$fieldname').style.visibility = 'hidden';
					document.getElementById('snap_$fieldname').style.visibility = 'visible';
				} else {
					alert('Press OK to release other cameras before continue');
				}
			}
			function snap_$fieldname() {
				Webcam.snap(function(data_uri) {document.getElementById('$fieldname').value = data_uri;});
				Webcam.freeze();
				document.getElementById('snap_$fieldname').style.visibility = 'hidden';
				document.getElementById('redo_$fieldname').style.visibility = 'visible';
				document.getElementById('ok_$fieldname').style.visibility = 'visible';
			}
			function redo_$fieldname() {
				Webcam.unfreeze();
				document.getElementById('$fieldname').value = null;
				document.getElementById('redo_$fieldname').style.visibility = 'hidden';
				document.getElementById('ok_$fieldname').style.visibility = 'hidden';
				document.getElementById('snap_$fieldname').style.visibility = 'visible';
			}
			function ok_$fieldname() {
				Webcam.reset();
				document.getElementById('preview_$fieldname').innerHTML = '<img src=\"' + document.getElementById('$fieldname').value + '\"/>';
				document.getElementById('redo_$fieldname').style.visibility = 'hidden';
				document.getElementById('ok_$fieldname').style.visibility = 'hidden';
				document.getElementById('start_$fieldname').style.visibility = 'visible';
				var function_$fieldname = document.getElementsByName('function[$fieldname]')[0];
				if (function_$fieldname.value == 'NULL') {
					function_$fieldname.value = ''
				}
			}
			");
			$return .= "<input type=button id='start_$fieldname' value='Start'>" . Adminer\script("qs('#start_$fieldname').onclick = start_$fieldname;");
			$return .= "<input type=button id='snap_$fieldname' value='Snap' style='visibility:hidden;'>" . Adminer\script("qs('#snap_$fieldname').onclick = snap_$fieldname;");
			$return .= "<input type=button id='redo_$fieldname' value='Redo' style='visibility:hidden;'>" . Adminer\script("qs('#redo_$fieldname').onclick = redo_$fieldname;");
			$return .= "<input type=button id='ok_$fieldname' value='OK' style='visibility:hidden;'>" . Adminer\script("qs('#ok_$fieldname').onclick = ok_$fieldname;");
			return $return;
		}
		// no return means return NULL, which trigger this method in other plugins until reach the original overloaded method
	}

	// receives the mime64-serialized image, decode, check/create folder, store and returns the filename. False otherwise
	function processInput($field, $value, $function = "") {
		if (preg_match($this->fieldsufix, $field["field"], $regs)) {
			if ($_GET["edit"] != "") {
				$table = ($_GET["edit"] ? $_GET["edit"] : $_GET["select"]);
				$fieldname = "$field[field]";
				$filename = $fieldname . date('_ymd_His') . '.jpg';
				$uploadfullpath = "$this->uploadPath/$_GET[db]/$table/$field[field]";
				$img_mime = $_POST[$fieldname];
				if (empty($img_mime)) {
					return false;
				}
				$img_mime = str_replace('data:image/jpeg;base64,', '', $img_mime);
				$img_mime = str_replace(' ', '+', $img_mime);
				$img_blob = base64_decode($img_mime);
				// create sub-directory if needed
				if (!file_exists($uploadfullpath)) {
					if (!mkdir($uploadfullpath, 0770, true)) {
						return false;
					}
				}
				// create the photo file in its final location
				if (! file_put_contents("$uploadfullpath/$filename", $img_blob) ) {
					return false;
				}
			} else {
				$filename = $value;
			}
			return Adminer\q($filename);
		}
	}

	function selectVal($val, &$link, $field, $original) {
		if ($val != "" && preg_match($this->fieldsufix, $field["field"], $regs)) {
			$link = "$this->displayPath/$_GET[db]/$_GET[select]/$field[field]/$val";
		}
	}

}
