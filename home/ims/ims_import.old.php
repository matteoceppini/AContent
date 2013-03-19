<?php
/************************************************************************/
/* AContent                                                             */
/************************************************************************/
/* Copyright (c) 2010                                                   */
/* Inclusive Design Institute                                           */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

define('TR_INCLUDE_PATH', '../../include/');
require_once(TR_INCLUDE_PATH.'vitals.inc.php');

require_once(TR_INCLUDE_PATH.'classes/Utility.class.php'); /* for clr_dir() and preImportCallBack and dirsize() */
require_once(TR_INCLUDE_PATH.'classes/DAO/UsersDAO.class.php'); /* for clr_dir() and preImportCallBack and dirsize() */
require_once(TR_INCLUDE_PATH.'classes/DAO/CoursesDAO.class.php'); /* for clr_dir() and preImportCallBack and dirsize() */
require_once(TR_INCLUDE_PATH.'classes/DAO/ContentDAO.class.php'); /* for clr_dir() and preImportCallBack and dirsize() */
require_once(TR_INCLUDE_PATH.'../home/classes/ContentUtility.class.php'); /* for clr_dir() and preImportCallBack and dirsize() */
require_once(TR_INCLUDE_PATH.'lib/filemanager.inc.php'); /* for clr_dir() and preImportCallBack and dirsize() */
require_once(TR_INCLUDE_PATH.'lib/pclzip.lib.php');
require_once(TR_INCLUDE_PATH.'lib/qti.inc.php'); 
//require(TR_INCLUDE_PATH.'classes/QTI/QTIParser.class.php');	
require_once(TR_INCLUDE_PATH.'classes/QTI/QTIImport.class.php');
require_once(TR_INCLUDE_PATH.'classes/A4a/A4aImport.class.php');
//require(TR_INCLUDE_PATH.'../tools/ims/ns.inc.php');	//namespace, no longer needs, delete it after it's stable.
require_once(TR_INCLUDE_PATH.'classes/Weblinks/WeblinksParser.class.php');

global $_current_user;
/* make sure the user has author privilege */
if (!isset($_current_user) || !$_current_user->isAuthor())
{
	$msg->addError('NO_PRIV');
	include(TR_INCLUDE_PATH.'header.inc.php');
	$msg->printAll(); 
	include(TR_INCLUDE_PATH.'footer.inc.php');
	exit;
}

/* to avoid timing out on large files */
@set_time_limit(0);
$_SESSION['done'] = 1;

$html_head_tags = array("style", "script");

$package_base_path = '';
$xml_base_path = '';
$element_path = array();
$imported_glossary = array();
$character_data = '';
$test_message = '';
$content_type = '';
$skip_ims_validation = false;


/**
 * Validate all the XML in the package, including checking XSDs, missing data.
 * @param	string		the path of the directory that contains all the package files
 * @return	boolean		true if every file exists in the manifest, false if any is missing.
 */
function checkResources($import_path){
	global $items, $msg, $skip_ims_validation;

	if (!is_dir($import_path)){
		return;
	}

	//if the package has access for all content, skip validation for now. 
	//todo: import the XSD into our validator
	if ($skip_ims_validation){
		return true;
	}

	//generate a file tree
	$data = rscandir($import_path);

	//check if every file is presented in the manifest
	foreach($data as $filepath){
		$filepath = substr($filepath, strlen($import_path));

		//validate xml via its xsd/dtds
		if (preg_match('/(.*)\.xml/', $filepath)){
			$dom = new DOMDocument();
			$dom->load(realpath($import_path.$filepath));

 			if (!@$dom->schemaValidate('main.xsd')){
				$msg->addError('MANIFEST_FAILED_VALIDATION - '.$filepath);
			}
			//if this is the manifest file, we do not have to check for its existance.
			if (preg_match('/(.*)imsmanifest\.xml/', $filepath)){
				continue;
			}
		}

		$flag = false;
		$file_exists_in_manifest = false;

		//check if every file in manifest indeed exists
		foreach($items as $name=>$fileinfo){
			if (is_array($fileinfo['file'])){
				if(in_array($filepath, $fileinfo['file'])){
					$file_exists_in_manifest = true;

					//validate the xml by its schema
					if (preg_match('/imsqti\_(.*)/', $fileinfo['type'])){
						$qti = new QTIParser($fileinfo['type']);
						$xml_content = @file_get_contents($import_path . $fileinfo['href']);
						$qti->parse($xml_content);
						if ($msg->containsErrors()){
							$flag = false;
						} else {
							$flag = true;
						}
					} else {
						$flag = true;
					}
				}
			}
		}

		//check if all the files exists in the manifest, if not, throw error.
		if (!$file_exists_in_manifest){
			$msg->addError('MANIFEST_NOT_WELLFORM: MISSING REFERENCES');
			break;
		}

		if ($flag == false){
			//add an error message if it doesn't have any. 
			if (!$msg->containsErrors()){
				$msg->addError('MANIFEST_NOT_WELLFORM: MISSING REFERENCES');
			}
			return false;
		}
	}
	return true;
}

/*
 * @example rscandir(dirname(__FILE__).'/'));
 * @param string $base
 * @param array $omit
 * @param array $data
 * @return array
 */
function rscandir($base='', &$data=array()) {
 
  $array = array_diff(scandir($base), array('.', '..')); # remove ' and .. from the array */
  
  foreach($array as $value) : /* loop through the array at the level of the supplied $base */
 
    if (is_dir($base.$value)) : /* if this is a directory */
//	  don't save the directory name
//	  $data[] = $base.$value.'/'; /* add it to the $data array */
      $data = rscandir($base.$value.'/', $data); /* then make a recursive call with the
      current $value as the $base supplying the $data array to carry into the recursion */
     
    elseif (is_file($base.$value)) : /* else if the current $value is a file */
      $data[] = $base.$value; /* just add the current $value to the $data array */
     
    endif;
   
  endforeach;
 
  return $data; // return the $data array
 
}

/**
 * Function to restructure the $items.  So that old import will merge the top page into its children, and
 * create a new folder on top of it
 */
function rehash($items){
	global $order;
	$parent_page_maps = array();	//old=>new
	$temp_popped_items = array();
	$rehashed_items = array();	//the reconstructed array
	foreach($items as $id => $content){
		$parent_obj = $items[$content['parent_content_id']];
		$rehashed_items[$id] = $content;	//copy

		//first check if there exists a mapping for this item, if so, simply replace is and next.
		if (isset($parent_page_maps[$content['parent_content_id']])){
			$rehashed_items [$id]['parent_content_id'] = $parent_page_maps[$content['parent_content_id']];
			$rehashed_items [$id]['ordering']++;
		} 
		//If its parent page is a top page and have an identiferref
		else if (isset($parent_obj) && isset($parent_obj['href'])){			
			if (!isset($parent_obj['href'])){
				//check if this top page is already a folder, if so, next.
				continue;
			}
			//else, make its parent page to a folder
			$new_item['title'] = $parent_obj['title'];
			$new_item['parent_content_id'] = $parent_obj['parent_content_id'];
			$new_item['ordering'] = $parent_obj['ordering'];

    		//assign this new parent folder to the pending items array
			$new_item_name = $content['parent_content_id'].'_FOLDER';
			//a not so brilliant way to append the folder in its appropriate position
			$reordered_hashed_items = array();  //use to store the new rehashed item with the correct item order
			foreach($rehashed_items as $rh_id=>$rh_content){
			    if ($rh_id == $content['parent_content_id']){
			        //add the folder in before the parent subpage.
			        $reordered_hashed_items[$new_item_name] = $new_item;
			    }
			    $reordered_hashed_items[$rh_id] = $rh_content;  //clone
			}
			$rehashed_items = $reordered_hashed_items;  //replace it back
			unset($reordered_hashed_items);
			$parent_page_maps[$content['parent_content_id']] = $new_item_name;  //save this page on the hash map

			//reconstruct the parent
			$rehashed_items[$content['parent_content_id']]['parent_content_id'] = $parent_page_maps[$content['parent_content_id']];
			$rehashed_items[$content['parent_content_id']]['ordering'] = 0; //always the first one.

			//reconstruct itself
			$rehashed_items[$id]['parent_content_id'] = $parent_page_maps[$content['parent_content_id']];
			$rehashed_items[$id]['ordering']++;

		}
	}

	return $rehashed_items;
}


/** 
 * This function will take the test accessment XML and add these to the database.
 * @param	string	The path of the XML, without the import_path.
 * @param	mixed	An item singleton.  Contains the info of this item, namely, the accessment details.
 *					The item must be an object created by the ims class.
 * @param	string	the import path
 * @return	mixed	An Array that contains all the question IDs that have been imported.
 */
 function addQuestions($xml, $item, $import_path){
	$qti_import = new QTIImport($import_path);

	$tests_xml = $import_path.$xml;
	
	//Mimic the array for now.
	$test_attributes['resource']['href'] = $item['href'];
	$test_attributes['resource']['type'] = preg_match('/imsqti_xmlv1p2/', $item['type'])==1?'imsqti_xmlv1p2':'imsqti_xmlv1p1';
	$test_attributes['resource']['file'] = $item['file'];

	//Get the XML file out and start importing them into our database.
	//TODO: See question_import.php 287-289.
	$qids = $qti_import->importQuestions($test_attributes);

	return $qids;
 }



	/* called at the start of en element */
	/* builds the $path array which is the path from the root to the current element */
	function startElement($parser, $name, $attrs) {
		global $items, $path, $package_base_path, $import_path;
		global $element_path;
		global $xml_base_path, $test_message, $content_type;
		global $current_identifier, $msg;

		if ($element_path === array('manifest', 'metadata', 'imsmd:lom', 'imsmd:general', 'imsmd:title') && $name == 'imsmd:langstring') {
			global $package_primay_lang;
			$package_primay_lang = trim($attrs['xml:lang']);
		}
		
		if ($name == 'manifest' && isset($attrs['xml:base']) && $attrs['xml:base']) {
			$xml_base_path = $attrs['xml:base'];
		} else if ($name == 'file') {
			// check if it misses file references
			if(!isset($attrs['href']) || $attrs['href']==''){
				$msg->addError('MANIFEST_NOT_WELLFORM');
			}

			// special case for webCT content packages that don't specify the `href` attribute 
			// with the `<resource>` element.
			// we take the `href` from the first `<file>` element.
			if (isset($items[$current_identifier]) && ($items[$current_identifier]['href'] == '')) {
				$attrs['href'] = urldecode($attrs['href']);
				$items[$current_identifier]['href'] = $attrs['href'];
			}

			$temp_path = pathinfo($attrs['href']);
			$temp_path = explode('/', $temp_path['dirname']);

			//for IMSCC, assume that all resources lies in the same folder, except styles.css
			if ($items[$current_identifier]['type']=='webcontent'){
				if ($package_base_path=="") {
					$package_base_path = $temp_path;
				} 
				elseif (is_array($package_base_path) && $content_type != 'IMS Common Cartridge') {
					//if this is a content package, we want only intersection
					$package_base_path = array_intersect($package_base_path, $temp_path);
					$temp_path = $package_base_path;
				}
				//added these 2 lines in so that pictures would load.  making the elseif above redundant.
				//if there is a bug for pictures not load, then it's the next 2 lines.
				$package_base_path = array_intersect($package_base_path, $temp_path);
				$temp_path = $package_base_path;
			}
			$items[$current_identifier]['new_path'] = implode('/', $temp_path);	
			if (	isset($_POST['allow_test_import']) && isset($items[$current_identifier]) 
						&& preg_match('/((.*)\/)*tests\_[0-9]+\.xml$/', $attrs['href'])) {
				$items[$current_identifier]['tests'][] = $attrs['href'];
			} 
			if (	isset($_POST['allow_a4a_import']) && isset($items[$current_identifier])) {
				$items[$current_identifier]['a4a_import_enabled'] = true;
			}
		} else if (($name == 'item') && ($attrs['identifierref'] != '')) {
			$path[] = $attrs['identifierref'];
		} else if (($name == 'item') && ($attrs['identifier'])) {
			$path[] = $attrs['identifier'];
//		} else if (($name == 'resource') && is_array($items[$attrs['identifier']]))  {
		} else if (($name == 'resource')) {
			$current_identifier = $attrs['identifier'];
			$items[$current_identifier]['type'] = $attrs['type'];
			if ($attrs['href']) {
				$attrs['href'] = urldecode($attrs['href']);

				$items[$attrs['identifier']]['href'] = $attrs['href'];

				// href points to a remote url
				if (preg_match('/^http.*:\/\//', trim($attrs['href'])))
					$items[$attrs['identifier']]['new_path'] = '';
				else // href points to local file
				{
					$temp_path = pathinfo($attrs['href']);
					$temp_path = explode('/', $temp_path['dirname']);
					if (empty($package_base_path)) {
						$package_base_path = $temp_path;
					} 
					$items[$attrs['identifier']]['new_path'] = implode('/', $temp_path);
				}
			}
		} else if ($name=='dependency' && $attrs['identifierref']!='') {
			//if there is a dependency, attach it to the item array['file']
			$items[$current_identifier]['dependency'][] = $attrs['identifierref'];
		}
		if (($name == 'item') && ($attrs['parameters'] != '')) {
			$items[$attrs['identifierref']]['test_message'] = $attrs['parameters'];
		}
		if ($name=='file'){
			if(!isset($items[$current_identifier]) && $attrs['href']!=''){
				$items[$current_identifier]['href']	 = $attrs['href'];
			}
			if (file_exists($import_path.$attrs['href'])){
				$items[$current_identifier]['file'][] = $attrs['href'];
			} else {
				$msg->addError('IMS_FILES_MISSING');
			}
		}		
		if ($name=='cc:authorizations'){
			//don't have authorization setup.
			$msg->addError('IMS_AUTHORIZATION_NOT_SUPPORT');
		}
	array_push($element_path, $name);
}

	/* called when an element ends */
	/* removed the current element from the $path */
	function endElement($parser, $name) {
		global $path, $element_path, $my_data, $items;
		global $current_identifier, $skip_ims_validation;
		global $msg, $content_type;		
		static $resource_num = 0;
		
		if ($name == 'item') {
			array_pop($path);
		} 

		//check if this is a test import
		if ($name == 'schema'){
			if (trim($my_data)=='IMS Question and Test Interoperability'){			
				$msg->addError('IMPORT_FAILED');
			} 
			$content_type = trim($my_data);
		}

		//Handles A4a
		if ($current_identifier != ''){
			$my_data = trim($my_data);
			$last_file_name = $items[$current_identifier]['file'][(sizeof($items[$current_identifier]['file']))-1];

			if ($name=='originalAccessMode'){				
				if (in_array('accessModeStatement', $element_path)){
					$items[$current_identifier]['a4a'][$last_file_name][$resource_num]['access_stmt_originalAccessMode'][] = $my_data;
				} elseif (in_array('adaptationStatement', $element_path)){
					$items[$current_identifier]['a4a'][$last_file_name][$resource_num]['adapt_stmt_originalAccessMode'][] = $my_data;
				}			
			} elseif (($name=='language') && in_array('accessModeStatement', $element_path)){
				$items[$current_identifier]['a4a'][$last_file_name][$resource_num]['language'][] = $my_data;
			} elseif ($name=='hasAdaptation') {
				$items[$current_identifier]['a4a'][$last_file_name][$resource_num]['hasAdaptation'][] = $my_data;
			} elseif ($name=='isAdaptationOf'){
				$items[$current_identifier]['a4a'][$last_file_name][$resource_num]['isAdaptationOf'][] = $my_data;
			} elseif ($name=='accessForAllResource'){
				/* the head node of accessForAll Metadata, if this exists in the manifest. Skip XSD validation,
				 * because A4a doesn't have a xsd yet.  Our access for all is based on ISO which will not pass 
				 * the current IMS validation.  
				 * Also, since ATutor is the only one (as of Oct 21, 2009) that exports IMS with access for all
				 * content, we can almost assume that any ims access for all content is by us, and is valid. 
				 */
				$skip_ims_validation = true;
				$resource_num++;
			} elseif($name=='file'){
				$resource_num = 0;	//reset resournce number to 0 when the file tags ends
			}
		}

		if ($element_path === array('manifest', 'metadata', 'imsmd:lom', 'imsmd:general', 'imsmd:title', 'imsmd:langstring')) {
			global $package_base_name;
			$package_base_name = trim($my_data);
		}

		if ($element_path === array('manifest', 'metadata', 'imsmd:lom', 'imsmd:general', 'imsmd:description', 'imsmd:langstring')) {
			global $package_base_description;
			$package_base_description = trim($my_data);
		}

		array_pop($element_path);
		$my_data = '';
	}

	/* called when there is character data within elements */
	/* constructs the $items array using the last entry in $path as the parent element */
	function characterData($parser, $data){
		global $path, $items, $order, $my_data, $element_path;
		global $current_identifier;

		$str_trimmed_data = trim($data);
				
		if (!empty($str_trimmed_data)) {
			$size = count($path);
			if ($size > 0) {
				$current_item_id = $path[$size-1];
				if ($size > 1) {
					$parent_item_id = $path[$size-2];
				} else {
					$parent_item_id = 0;
				}

				if (isset($items[$current_item_id]['parent_content_id']) && is_array($items[$current_item_id])) {

					/* this item already exists, append the title		*/
					/* this fixes {\n, \t, `, &} characters in elements */

					/* horible kludge to fix the <ns2:objectiveDesc xmlns:ns2="http://www.utoronto.ca/atrc/tile/xsd/tile_objective"> */
					/* from TILE */
					if (in_array('accessForAllResource', $element_path)){
						//skip this tag
					} elseif ($element_path[count($element_path)-1] != 'ns1:objectiveDesc') {
						$items[$current_item_id]['title'] .= $data;
					}
	
				} else {
					$order[$parent_item_id]++;
					$item_tmpl = array(	'title'			=> $data,
										'parent_content_id' => $parent_item_id,
										'ordering'			=> $order[$parent_item_id]-1);
					//append other array values if it exists
					if (is_array($items[$current_item_id])){
						$items[$current_item_id] = array_merge($items[$current_item_id], $item_tmpl);
					} else {
						$items[$current_item_id] = $item_tmpl;
					}
				}
			}
		}

		$my_data .= $data;
	}

	/* glossary parser: */
	function glossaryStartElement($parser, $name, $attrs) {
		global $element_path;

		array_push($element_path, $name);
	}

	/* called when an element ends */
	/* removed the current element from the $path */
	function glossaryEndElement($parser, $name) {
		global $element_path, $my_data, $imported_glossary;
		static $current_term;

		if ($element_path === array('glossary', 'item', 'term')) {
			$current_term = $my_data;

		} else if ($element_path === array('glossary', 'item', 'definition')) {
			$imported_glossary[trim($current_term)] = trim($my_data);
		}

		array_pop($element_path);
		$my_data = '';
	}

	function glossaryCharacterData($parser, $data){
		global $my_data;

		$my_data .= $data;
	}

if (!isset($_POST['submit']) && !isset($_POST['cancel'])) {
	/* just a catch all */
	
	$errors = array('FILE_MAX_SIZE', ini_get('post_max_size'));
	$msg->addError($errors);

	header('Location: ../index.php');
	exit;
} else if (isset($_POST['cancel'])) {
	$msg->addFeedback('IMPORT_CANCELLED');

	header('Location: ../index.php');
	exit;
}

if (isset($_POST['url']) && ($_POST['url'] != 'http://') ) {
	if ($content = @file_get_contents($_POST['url'])) {

		// save file to /content/
		$filename = substr(time(), -6). '.zip';
		$full_filename = TR_TEMP_DIR . $filename;

		if (!$fp = fopen($full_filename, 'w+b')) {
			echo "Cannot open file ($filename)";
			exit;
		}

		if (fwrite($fp, $content, strlen($content) ) === FALSE) {
			echo "Cannot write to file ($filename)";
			exit;
		}
		fclose($fp);
	}	
	$_FILES['file']['name']     = $filename;
	$_FILES['file']['tmp_name'] = $full_filename;
	$_FILES['file']['size']     = strlen($content);
	unset($content);
	$url_parts = pathinfo($_POST['url']);
	$package_base_name_url = $url_parts['basename'];
}
$ext = pathinfo($_FILES['file']['name']);
$ext = $ext['extension'];

if ($ext != 'zip') {
	$msg->addError('IMPORTDIR_IMS_NOTVALID');
} else if ($_FILES['file']['error'] == 1) {
	$errors = array('FILE_MAX_SIZE', ini_get('upload_max_filesize'));
	$msg->addError($errors);
} else if ( !$_FILES['file']['name'] || (!is_uploaded_file($_FILES['file']['tmp_name']) && !$_POST['url'])) {
	$msg->addError('FILE_NOT_SELECTED');
} else if ($_FILES['file']['size'] == 0) {
	$msg->addError('IMPORTFILE_EMPTY');
} 

if ($msg->containsErrors()) {
	if (isset($_GET['tile'])) {
		header('Location: '.$_base_path.'tools/tile/index.php');
	} else {
		header('Location: ../index.php');
	}
	exit;
}

/* check if ../content/import/ exists */
$import_path = TR_TEMP_DIR . 'import/';
$content_path = TR_TEMP_DIR;

if (!is_dir($import_path)) {
	if (!@mkdir($import_path, 0700)) {
		$msg->addError('IMPORTDIR_FAILED');
	}
}

$import_path .= Utility::getRandomStr(16).'/';
if (is_dir($import_path)) {
	clr_dir($import_path);
}

if (!@mkdir($import_path, 0700)) {
	$msg->addError('IMPORTDIR_FAILED');
}

if ($msg->containsErrors()) {
	if (isset($_GET['tile'])) {
		header('Location: '.$_base_path.'tools/tile/index.php');
	} else {
		header('Location: ../index.php');
	}
	exit;
}

/* extract the entire archive into TR_COURSE_CONTENT . import/$course using the call back function to filter out php files */
error_reporting(0);
$archive = new PclZip($_FILES['file']['tmp_name']);
if ($archive->extract(	PCLZIP_OPT_PATH,	$import_path,
						PCLZIP_CB_PRE_EXTRACT,	'preImportCallBack') == 0) {
	$msg->addError('IMPORT_FAILED');
	echo 'Error : '.$archive->errorInfo(true);
	clr_dir($import_path);
	header('Location: ../index.php');
	exit;
}
error_reporting(TR_ERROR_REPORTING);

/* get the course's max_quota */
//$sql	= "SELECT max_quota FROM ".TABLE_PREFIX."courses WHERE course_id=$course_id";
//$result = mysql_query($sql, $db);
//$q_row	= mysql_fetch_assoc($result);
//
//if ($q_row['max_quota'] != TR_COURSESIZE_UNLIMITED) {
//
//	if ($q_row['max_quota'] == TR_COURSESIZE_DEFAULT) {
//		$q_row['max_quota'] = $MaxCourseSize;
//	}
//	$totalBytes   = dirsize($import_path);
//	$course_total = dirsize($import_path);
//	$total_after  = $q_row['max_quota'] - $course_total - $totalBytes + $MaxCourseFloat;
//
//	if ($total_after < 0) {
//		/* remove the content dir, since there's no space for it */
//		$errors = array('NO_CONTENT_SPACE', number_format(-1*($total_after/TR_KBYTE_SIZE), 2 ) );
//		$msg->addError($errors);
//		
//		clr_dir($import_path);
//
//		if (isset($_GET['tile'])) {
//			header('Location: '.$_base_path.'tools/tile/index.php');
//		} else {
//			header('Location: index.php');
//		}
//		exit;
//	}
//}


$items = array(); /* all the content pages */
$order = array(); /* keeps track of the ordering for each content page */
$path  = array();  /* the hierarchy path taken in the menu to get to the current item in the manifest */
$dependency_files = array(); /* the file path for the dependency files */

/*
$items[content_id/resource_id] = array(
									'title'
									'real_content_id' // calculated after being inserted
									'parent_content_id'
									'href'
									'ordering'
									);
*/

$ims_manifest_xml = @file_get_contents($import_path.'imsmanifest.xml');

if ($ims_manifest_xml === false) {
	$msg->addError('NO_IMSMANIFEST');

	if (file_exists($import_path . 'atutor_backup_version')) {
		$msg->addError('NO_IMS_BACKUP');
	}

	clr_dir($import_path);

	if (isset($_GET['tile'])) {
		header('Location: '.$_base_path.'tools/tile/index.php');
	} else {
		header('Location: ../index.php');
	}
	exit;
}

$xml_parser = xml_parser_create();

xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false); /* conform to W3C specs */
xml_set_element_handler($xml_parser, 'startElement', 'endElement');
xml_set_character_data_handler($xml_parser, 'characterData');

if (!xml_parse($xml_parser, $ims_manifest_xml, true)) {
	die(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($xml_parser)),
				xml_get_current_line_number($xml_parser)));
}

xml_parser_free($xml_parser);
// skip glossary
/* check if the glossary terms exist */
//$glossary_path = '';
//if ($content_type == 'IMS Common Cartridge'){
//	$glossary_path = 'GlossaryItem/';
//}
//if (file_exists($import_path . $glossary_path . 'glossary.xml')){
//	$glossary_xml = @file_get_contents($import_path.$glossary_path.'glossary.xml');
//	$element_path = array();
//
//	$xml_parser = xml_parser_create();
//
//	/* insert the glossary terms into the database (if they're not in there already) */
//	/* parse the glossary.xml file and insert the terms */
//	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false); /* conform to W3C specs */
//	xml_set_element_handler($xml_parser, 'glossaryStartElement', 'glossaryEndElement');
//	xml_set_character_data_handler($xml_parser, 'glossaryCharacterData');
//
//	if (!xml_parse($xml_parser, $glossary_xml, true)) {
//		die(sprintf("XML error: %s at line %d",
//					xml_error_string(xml_get_error_code($xml_parser)),
//					xml_get_current_line_number($xml_parser)));
//	}
//	xml_parser_free($xml_parser);
//	$contains_glossary_terms = true;
//	foreach ($imported_glossary as $term => $defn) {
//		if (!$glossary[urlencode($term)]) {
//			$sql = "INSERT INTO ".TABLE_PREFIX."glossary VALUES (NULL, $_SESSION[course_id], '$term', '$defn', 0)";
//			mysql_query($sql, $db);	
//		}
//	}
//}

// Check if all the files exists in the manifest, iff it's a IMS CC package.
if ($content_type == 'IMS Common Cartridge') {
	checkResources($import_path);
}

// Check if there are any errors during parsing.
if ($msg->containsErrors()) {
	if (isset($_GET['tile'])) {
		header('Location: '.$_base_path.'tools/tile/index.php');
	} else {
		header('Location: ../index.php');
	}
	exit;
}

/* initialize DAO objects */
$coursesDAO = new CoursesDAO();
$contentDAO = new ContentDAO();

/* generate a unique new package base path based on the package file name and date as needed. */
/* the package name will be the dir where the content for this package will be put, as a result */
/* the 'content_path' field in the content table will be set to this path. */
/* $package_base_name_url comes from the URL file name (NOT the file name of the actual file we open)*/
if (!$package_base_name && $package_base_name_url) {
	$package_base_name = substr($package_base_name_url, 0, -4);
} else if (!$package_base_name) {
	$package_base_name = substr($_FILES['file']['name'], 0, -4);
}

// create course
if (isset($_POST['hide_course']))
	$access = 'private';
else
	$access = 'public';

$course_id = $coursesDAO->Create($_SESSION['user_id'], 'top', $access, $package_base_name, $package_base_description, 
             '', '', '', '', $package_primay_lang, '', '');

$package_base_name = strtolower($package_base_name);
$package_base_name = str_replace(array('\'', '"', ' ', '|', '\\', '/', '<', '>', ':'), '_' , $package_base_name);
$package_base_name = preg_replace("/[^A-Za-z0-9._\-]/", '', $package_base_name);

if (is_dir(TR_TEMP_DIR . $course_id.'/'.$package_base_name)) {
	$package_base_name .= '_'.date('ymdHis');
}

if ($package_base_path) {
	$package_base_path = implode('/', $package_base_path);
} elseif (empty($package_base_path)){
	$package_base_path = '';
}

if ($xml_base_path) {
	$package_base_path = $xml_base_path . $package_base_path;

	mkdir(TR_TEMP_DIR .$course_id.'/'.$xml_base_path);
	$package_base_name = $xml_base_path . $package_base_name;
}

/* get the top level content ordering offset */
//$sql	= "SELECT MAX(ordering) AS ordering FROM ".TABLE_PREFIX."content WHERE course_id=$_SESSION[course_id] AND content_parent_id=$cid";
//$result = mysql_query($sql, $db);
//$row	= mysql_fetch_assoc($result);
$order_offset = $contentDAO->getMaxOrdering($course_id, 0); /* it's nice to have a real number to deal with */
$lti_offset = array();	//since we don't need lti tools, the ordering needs to be subtracted
//reorder the items stack
$items = rehash($items);

foreach ($items as $item_id => $content_info) 
{	
	//formatting field, default 1
	$content_formatting = 1;	//CONTENT_TYPE_CONTENT

	//if this is any of the LTI tools, skip it. (ie. Discussion Tools, Weblinks, etc)
	//take this condition out once the LTI tool kit is implemented.
	if ($content_info['type']=='imsdt_xmlv1p0'){
		$lti_offset[$content_info['parent_content_id']]++;
		continue;
	}

	//don't want to display glossary as a page
	if ($content_info['href']== $glossary_path . 'glossary.xml'){
		continue;
	}

	//handle the special case of cc import, where there is no content association. The resource should
	//still be imported.
	if(!isset($content_info['parent_content_id'])){
		//if this is a question bank 
		if ($content_info['type']=="imsqti_xmlv1p2/imscc_xmlv1p0/question-bank"){
			addQuestions($content_info['href'], $content_info, $import_path);
		}
	}

	//if it has no title, most likely it is not a page but just a normal item, skip it
	if (!isset($content_info['title'])){
		continue;
	}
	
	//check dependency immediately, then handles it
	$head = '';
	if (is_array($content_info['dependency']) && !empty($content_info['dependency'])){
		foreach($content_info['dependency'] as $dependency_ref){
			//handle styles	
			if (preg_match('/(.*)\.css$/', $items[$dependency_ref]['href'])){
				//calculate where this is based on our current base_href. 
				//assuming the dependency folders are siblings of the item
				$head = '<link rel="stylesheet" type="text/css" href="../'.$items[$dependency_ref]['href'].'" />';
			}
		}
	}
	
	// remote href
	if (preg_match('/^http.*:\/\//', trim($content_info['href'])) )
	{
		$content = '<a href="'.$content_info['href'].'" target="_blank">'.$content_info['title'].'</a>';
	}
	else
	{
		if (isset($content_info['href'], $xml_base_path)) {
			$content_info['href'] = $xml_base_path . $content_info['href'];
		}
		if (!isset($content_info['href'])) {
			// this item doesn't have an identifierref. so create an empty page.
			// what we called a folder according to v1.2 Content Packaging spec
			// Hop over
			$content = '';
			$ext = '';
			$last_modified = date('Y-m-d H:i:s');
		} else {
			$file_info = @stat($import_path.$content_info['href']);
			if ($file_info === false) {
				continue;
			}
		
			$path_parts = pathinfo($import_path.$content_info['href']);
			$ext = strtolower($path_parts['extension']);

			$last_modified = date('Y-m-d H:i:s', $file_info['mtime']);
		}
		if (in_array($ext, array('gif', 'jpg', 'bmp', 'png', 'jpeg'))) {
			/* this is an image */
			$content = '<img src="'.$content_info['href'].'" alt="'.$content_info['title'].'" />';
		} else if ($ext == 'swf') {
			/* this is flash */
            /* Using default size of 550 x 400 */

			$content = '<object type="application/x-shockwave-flash" data="' . $content_info['href'] . '" width="550" height="400"><param name="movie" value="'. $content_info['href'] .'" /></object>';

		} else if ($ext == 'mov') {
			/* this is a quicktime movie  */
            /* Using default size of 550 x 400 */

			$content = '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="550" height="400" codebase="http://www.apple.com/qtactivex/qtplugin.cab"><param name="src" value="'. $content_info['href'] . '" /><param name="autoplay" value="true" /><param name="controller" value="true" /><embed src="' . $content_info['href'] .'" width="550" height="400" controller="true" pluginspage="http://www.apple.com/quicktime/download/"></embed></object>';

		/* Oct 19, 2009
		 * commenting this whole chunk out.  It's part of my test import codes, not sure why it's here, 
		 * and I don't think it should be here.  Remove this whole comment after further testing and confirmation.
		 * @harris
		 *
			//Mimic the array for now.
			$test_attributes['resource']['href'] = $test_xml_file;
			$test_attributes['resource']['type'] = isset($items[$item_id]['type'])?'imsqti_xmlv1p2':'imsqti_xmlv1p1';
			$test_attributes['resource']['file'] = $items[$item_id]['file'];
//			$test_attributes['resource']['file'] = array($test_xml_file);

			//Get the XML file out and start importing them into our database.
			//TODO: See question_import.php 287-289.
			$qids = $qti_import->importQuestions($test_attributes);
		
		 */
		} else if ($ext == 'mp3') {
			$content = '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="200" height="15" codebase="http://www.apple.com/qtactivex/qtplugin.cab"><param name="src" value="'. $content_info['href'] . '" /><param name="autoplay" value="false" /><embed src="' . $content_info['href'] .'" width="200" height="15" autoplay="false" pluginspage="http://www.apple.com/quicktime/download/"></embed></object>';
		} else if (in_array($ext, array('wav', 'au'))) {
			$content = '<embed SRC="'.$content_info['href'].'" autostart="false" width="145" height="60"><noembed><bgsound src="'.$content_info['href'].'"></noembed></embed>';

		} else if (in_array($ext, array('txt', 'css', 'html', 'htm', 'csv', 'asc', 'tsv', 'xml', 'xsl'))) {
			/* this is a plain text file */
			$content = file_get_contents($import_path.$content_info['href']);
			if ($content === false) {
				/* if we can't stat() it then we're unlikely to be able to read it */
				/* so we'll never get here. */
				continue;
			}

			// get the contents of the 'head' element
			$head .= ContentUtility::getHtmlHeadByTag($content, $html_head_tags);
			
			// Specifically handle eXe package
			// NOTE: THIS NEEDS WORK! TO FIND A WAY APPLY EXE .CSS FILES ONLY ON COURSE CONTENT PART.
			// NOW USE OUR OWN .CSS CREATED SOLELY FOR EXE
			$isExeContent = false;

			// check xml file in eXe package
			if (preg_match("/<organization[ ]*identifier=\"eXe*>*/", $ims_manifest_xml))
			{
				$isExeContent = true;
			}

			// use ATutor's eXe style sheet as the ones from eXe conflicts with ATutor's style sheets
			if ($isExeContent)
			{
				$head = preg_replace ('/(<style.*>)(.*)(<\/style>)/ms', '\\1@import url(/docs/exestyles.css);\\3', $head);
			}

			// end of specifically handle eXe package

			$content = ContentUtility::getHtmlBody($content);
			if ($contains_glossary_terms) 
			{
				// replace glossary content package links to real glossary mark-up using [?] [/?]
				// refer to bug 3641, edited by Harris
				$content = preg_replace('/<a href="([.\w\d\s]+[^"]+)" target="body" class="at-term">([.\w\d\s&;"]+|.*)<\/a>/i', '[?]\\2[/?]', $content);
			}

			/* potential security risk? */
			if ( strpos($content_info['href'], '..') === false && !preg_match('/((.*)\/)*tests\_[0-9]+\.xml$/', $content_info['href'])) {
//				@unlink($import_path.$content_info['href']);
			}
		} else if ($ext) {
			/* non text file, and can't embed (example: PDF files) */
			$content = '<a href="'.$content_info['href'].'">'.$content_info['title'].'</a>';
		}	
	}
	$content_parent_id = $cid;
	if ($content_info['parent_content_id'] !== 0) {
		$content_parent_id = $items[$content_info['parent_content_id']]['real_content_id'];
		//if it's not there, use $cid
		if (!$content_parent_id){
			$content_parent_id = $cid;
		}
	}

	$my_offset = 0;
	if ($content_parent_id == $cid) {
		$my_offset = $order_offset;
	}

	/* replace the old path greatest common denomiator with the new package path. */
	/* we don't use str_replace, b/c there's no knowing what the paths may be	  */
	/* we only want to replace the first part of the path.	
	*/
	if ($package_base_path != '') {
		$content_info['new_path'] = $package_base_name . substr($content_info['new_path'], strlen($package_base_path));
	} else {
		$content_info['new_path'] = $package_base_name . '/' . $content_info['new_path'];
	}

	//handles weblinks
	if ($content_info['type']=='imswl_xmlv1p0'){
		$weblinks_parser = new WeblinksParser();
		$xml_content = @file_get_contents($import_path . $content_info['href']);
		$weblinks_parser->parse($xml_content);
		$content_info['title'] = $weblinks_parser->getTitle();
		$content = $weblinks_parser->getUrl();
		$content_folder_type = CONTENT_TYPE_WEBLINK;
		$content_formatting = 2;
	}
	$head = addslashes($head);
	$content_info['title'] = addslashes($content_info['title']);
	$content_info['test_message'] = addslashes($content_info['test_message']);

	//if this file is a test_xml, create a blank page instead, for imscc.
	if (preg_match('/((.*)\/)*tests\_[0-9]+\.xml$/', $content_info['href']) 
		|| preg_match('/imsqti\_(.*)/', $content_info['type'])) {
		$content = '';
	} else {
		$content = addslashes($content);
	}

	//check for content_type
	if ($content_formatting!=CONTENT_TYPE_WEBLINK){
		$content_folder_type = ($content==''?CONTENT_TYPE_FOLDER:CONTENT_TYPE_CONTENT);
	}

	$items[$item_id]['real_content_id'] = $contentDAO->Create($course_id, intval($content_parent_id), 
	                    ($content_info['ordering'] + $my_offset - $lti_offset[$content_info['parent_content_id']] + 1),
	                    $last_modified, 0, $content_formatting, "", $content_info['new_path'], $content_info['title'],
	                    $content, $head, 1, $content_info['test_message'], 0, $content_folder_type);
//	$sql= 'INSERT INTO '.TABLE_PREFIX.'content'
//	      . '(course_id, 
//	          content_parent_id, 
//	          ordering,
//	          last_modified, 
//	          revision, 
//	          formatting, 
//	          head,
//	          use_customized_head,
//	          keywords, 
//	          content_path, 
//	          title, 
//	          text,
//			  test_message,
//			  content_type) 
//	       VALUES 
//			     ('.$_SESSION['course_id'].','															
//			     .intval($content_parent_id).','		
//			     .($content_info['ordering'] + $my_offset - $lti_offset[$content_info['parent_content_id']] + 1).','
//			     .'"'.$last_modified.'",													
//			      0,'
//			     .$content_formatting.' ,"'
//			     . $head .'",
//			     1,
//			      "",'
//			     .'"'.$content_info['new_path'].'",'
//			     .'"'.$content_info['title'].'",'
//			     .'"'.$content.'",'
//				 .'"'.$content_info['test_message'].'",'
//				 .$content_folder_type.')';
//
//	$result = mysql_query($sql, $db) or die(mysql_error());

	/* get the content id and update $items */
//	$items[$item_id]['real_content_id'] = mysql_insert_id($db);

	/* get the tests associated with this content */
	if (!empty($items[$item_id]['tests']) || strpos($items[$item_id]['type'], 'imsqti_xmlv1p2/imscc_xmlv1p0') !== false){
		$qti_import = new QTIImport($import_path);

		if (isset($items[$item_id]['tests'])){
			$loop_var = $items[$item_id]['tests'];
		} else {
			$loop_var = $items[$item_id]['file'];
		}

		foreach ($loop_var as $array_id => $test_xml_file){
			//call subrountine to add the questions.
			$qids = addQuestions($test_xml_file, $items[$item_id], $import_path);
			
			//import test
			$tid = $qti_import->importTest($content_info['title']);

			//associate question and tests
			foreach ($qids as $order=>$qid){
				if (isset($qti_import->weights[$order])){
					$weight = round($qti_import->weights[$order]);
				} else {
					$weight = 0;
				}
				$new_order = $order + 1;
				$sql = "INSERT INTO " . TABLE_PREFIX . "tests_questions_assoc" . 
						"(test_id, question_id, weight, ordering, required) " .
						"VALUES ($tid, $qid, $weight, $new_order, 0)";
				$result = mysql_query($sql, $db);
			}

			//associate content and test
			$sql =	'INSERT INTO ' . TABLE_PREFIX . 'content_tests_assoc' . 
					'(content_id, test_id) ' .
					'VALUES (' . $items[$item_id]['real_content_id'] . ", $tid)";
			$result = mysql_query($sql, $db);
		
//			if (!$msg->containsErrors()) {
//				$msg->addFeedback('IMPORT_SUCCEEDED');
//			}
		}
	}

	/* get the a4a related xml */
	if (isset($items[$item_id]['a4a_import_enabled']) && isset($items[$item_id]['a4a']) && !empty($items[$item_id]['a4a'])) {
		$a4a_import = new A4aImport($items[$item_id]['real_content_id']);
		$a4a_import->setRelativePath($items[$item_id]['new_path']);
		$a4a_import->importA4a($items[$item_id]['a4a']);
	}
}

if ($package_base_path == '.') {
	$package_base_path = '';
}

// loop through the files outside the package folder, and copy them to its relative path
if (is_dir(TR_TEMP_DIR . 'import/'.$course_id.'/resources')) {
	$handler = opendir(TR_TEMP_DIR . 'import/'.$course_id.'/resources');
	while ($file = readdir($handler)){
		$filename = TR_TEMP_DIR . 'import/'.$course_id.'/resources/'.$file;
		if(is_file($filename)){
			@rename($filename, TR_TEMP_DIR .$course_id.'/'.$package_base_name.'/'.$file);
		}
	}
	closedir($handler);
}

$course_dir = TR_TEMP_DIR.$course_id.'/';

if (!is_dir($course_dir)) {
	if (!@mkdir($course_dir, 0700)) {
		$msg->addError('IMPORTDIR_FAILED');
	}
}


if (@rename($import_path.$package_base_path, $course_dir.$package_base_name) === false) {
	if (!$msg->containsErrors()) {
		$msg->addError('IMPORT_FAILED');
	}
}
//check if there are still resources missing
foreach($items as $idetails){
	$temp_path = pathinfo($idetails['href']);
	@rename($import_path.$temp_path['dirname'], $course_dir.$package_base_name . '/' . $temp_path['dirname']);
}
clr_dir($import_path);

if (isset($_POST['url'])) {
	@unlink($full_filename);
}


//if ($_POST['s_cid'] || $course_id){
	if (!$msg->containsErrors()) {
		$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
	}
	header('Location: ../course.php?cid='.$course_id);
	exit;
//} else {
//	if (!$msg->containsErrors()) {
//		$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
//	}
//	if ($_GET['tile']) {
//		header('Location: '.TR_BASE_HREF.'tools/tile/index.php');
//	} else {
//		header('Location: ../course.php?cid='.$course_id);
//	}
//	exit;
//}

?>
