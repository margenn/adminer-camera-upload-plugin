<?php

/** Edit fields ending with "_photo" with a camera upload interface and link to the uploaded photos from select
* the connection MUST be HTTPS otherwise you get a 'No supported webcam interface found'
* @link https://www.adminer.org/plugins/#use
* @author Marcelo Gennari, https://gren.com.br/
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 1.0.0 beta
*/
class AdminerCameraUpload {
	/** @access protected */
	var $uploadPath, $displayPath, $scripts;

	/**
	* @param string prefix for uploading data (create writable subdirectory for each table containing uploadable fields)
	* @param string prefix for displaying data, null stands for $uploadPath
	* @param string regular expression with allowed file extensions
	*/
	function __construct($uploadPath = "./photos/", $displayPath = null, $scripts = array("webcam.min.js")) {
		$this->uploadPath = $uploadPath;
		$this->displayPath = ($displayPath !== null ? $displayPath : $uploadPath);
		$this->scripts = $scripts;
	}

	function head() {
		foreach ($this->scripts as $script) {
			echo script_src($script);
		}
	}

	function editInput($table, $field, $attrs, $value) {
		if (preg_match('~(.*)_photo$~', $field["field"])) {
			$fieldname = "$field[field]";
			$rtn = "";
			if (! empty($value)) {
				$rtn .= "<div><a target=\"_blank\" href=\"$this->displayPath$table/$value\">$value</a></div>";
			}
			$rtn .= "<input type='hidden' id='$fieldname' name='$fieldname'>\n";
			$rtn .= "<div id='preview_$fieldname'></div>\n";
			$rtn .= script("
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
					alert('Save the other photos before continue');
				}
			}
			function snap_$fieldname() {
				Webcam.snap(function(data_uri) {document.getElementById('$fieldname').value = data_uri;});
				Webcam.freeze();
				document.getElementById('snap_$fieldname').style.visibility = 'hidden';
				document.getElementById('redo_$fieldname').style.visibility = 'visible';
				document.getElementById('save_$fieldname').style.visibility = 'visible';
			}
			function redo_$fieldname() {
				Webcam.unfreeze();
				document.getElementById('$fieldname').value = null;
				document.getElementById('redo_$fieldname').style.visibility = 'hidden';
				document.getElementById('save_$fieldname').style.visibility = 'hidden';
				document.getElementById('snap_$fieldname').style.visibility = 'visible';
			}
			function save_$fieldname() {
				Webcam.reset();
				document.getElementById('preview_$fieldname').innerHTML = '<img src=\"' + document.getElementById('$fieldname').value + '\"/>';
				document.getElementById('redo_$fieldname').style.visibility = 'hidden';
				document.getElementById('save_$fieldname').style.visibility = 'hidden';
				document.getElementById('start_$fieldname').style.visibility = 'visible';
			}
			");
			$rtn .= "<input type=button id='start_$fieldname' value='Start'>" . script("qs('#start_$fieldname').onclick = start_$fieldname;");
			$rtn .= "<input type=button id='snap_$fieldname' value='Snap' style='visibility:hidden;'>" . script("qs('#snap_$fieldname').onclick = snap_$fieldname;");
			$rtn .= "<input type=button id='redo_$fieldname' value='Redo' style='visibility:hidden;'>" . script("qs('#redo_$fieldname').onclick = redo_$fieldname;");
			$rtn .= "<input type=button id='save_$fieldname' value='Save' style='visibility:hidden;'>" . script("qs('#save_$fieldname').onclick = save_$fieldname;");
			return $rtn;
		}
	}

	function processInput($field, $value, $function = "") {
		if (preg_match('~(.*)_photo$~', $field["field"], $regs)) {
			if ($_GET["edit"] != "") {
				$table = ($_GET["edit"] != "" ? $_GET["edit"] : $_GET["select"]);
				$fieldname = "$field[field]";
				$filename = $fieldname . date('_ymd_His') . '.jpg';
				$fullfilepath = "$this->uploadPath$table/$filename";
				$img_mime = $_POST[$fieldname];
				if (empty($img_mime)) { return false; }
				$img_mime = str_replace('data:image/jpeg;base64,', '', $img_mime);
				$img_mime = str_replace(' ', '+', $img_mime);
				$img_blob = base64_decode($img_mime);
				$success = file_put_contents($fullfilepath, $img_blob);
				if (! $success ) { return false; }
			} else {
				$filename = $value;
			}
			return q($filename);
		}
	}

	function selectVal($val, &$link, $field, $original) {
		if ($val != "" && preg_match('~(.*)_photo$~', $field["field"], $regs)) {
			$link = "$this->displayPath" . $_GET['select'] . "/$val";
		}
	}

}
