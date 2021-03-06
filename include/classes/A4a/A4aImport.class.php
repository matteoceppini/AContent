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

require_once(TR_INCLUDE_PATH.'classes/A4a/A4a.class.php');

/**
 * Accessforall Import  class.
 * Based on the specification at: 
 *		http://www.imsglobal.org/accessibility/index.html
 *
 * @date	Oct 9th, 2008
 * @author	Harris Wong
 */
class A4aImport extends A4a {
	//Constructor
	function A4aImport($cid){
		parent::A4a($cid);		//call its parent
	}

	/** 
	 * Import AccessForAll
	 * @param	array	XML items generated by the IMS import
	 */
	function importA4a($items){
		//imported files, keep track of what file path that's been processed. Do not add repeated ones
		$imported_files = array();
		
		//use the items array data and insert it into the database.
		foreach ($items as $file_path => $a4a_resources){
			foreach ($a4a_resources as $resource){
				//If it has adaptation/alternative, this is a primary file.
				if (isset($resource['hasAdaptation']) && !empty($resource['hasAdaptation'])){
					//we only have one language in the table, [1], [2], etc will be the same
					$pri_lang = $resource['language'][0];	

					//insert primary resource
					$primary_id = $this->setPrimaryResource($this->cid, str_replace($this->relative_path, '', $file_path), $pri_lang);

					//get primary resource type
					$resources_attrs = $resource['access_stmt_originalAccessMode'];
					
					$attrs = $this->toResourceTypeId($resources_attrs);

					//insert primary resource type associations
					foreach ($attrs as $resource_type_id){
						$this->setPrimaryResourceType($primary_id, $resource_type_id);
					}

					//insert secondary resource
					$secondary_resources = $resource['hasAdaptation'];	//uri array
					foreach ($secondary_resources as $secondary_resource){
						//some paths will reference files above this directory, as a result
						//we will see ../, but since everything is under 'resources/', the relative_path
						//we can safely take it out.
						//@edited Dec 6th, imscc import uses relative paths, ims doesn't.
						if (substr($secondary_resource, 0, 7) == 'http://' || substr($secondary_resource, 0, 8) == 'https://') {
							$secondary_resource_with_relative_path = $secondary_resource;
						} else {
							$secondary_resource_with_relative_path = $this->relative_path.$secondary_resource;
						}

						$secondary_files = $items[$secondary_resource_with_relative_path];
						if (in_array($secondary_resource_with_relative_path, $imported_files)){
							continue;
						}
						$imported_files[] = $secondary_resource_with_relative_path;

						if (empty($secondary_files)){
						    //tweak: if this is empty, then most likely it is an ims import.
						    $secondary_resource = preg_replace('/^\.\.\//', '', $secondary_resource);
						    $secondary_files = $items[$this->relative_path.$secondary_resource];
						}
						
						//check if this secondary file is the adaptation of 
						// this primary file 
						foreach($secondary_files as $secondary_file){
							//isAdaptation is 1-to-1 mapping, save to use [0]
							if (substr($secondary_file['isAdaptationOf'][0], 0, 7) == 'http://' 
							   || substr($secondary_file['isAdaptationOf'][0], 0, 8) == 'https://') {
								$adaption_with_relative_path = $secondary_file['isAdaptationOf'][0];
							} else {
								$adaption_with_relative_path = $this->relative_path.$secondary_file['isAdaptationOf'][0];
							}
							
							if($adaption_with_relative_path == $file_path){
								$secondary_lang = $secondary_file['language'][0];

								//access_stmt_originalAccessMode cause we want the language for the secondary file.
								$secondary_attr = $this->toResourceTypeId($secondary_file['access_stmt_originalAccessMode']);
								$secondary_id = $this->setSecondaryResource($primary_id, $secondary_resource, $secondary_lang);

								//insert secondary resource type associations
								foreach ($secondary_attr as $secondary_resource_type_id){
									$this->setSecondaryResourceType($secondary_id, $secondary_resource_type_id);
 								}
								//break;	//1-to-1 mapping, no need to go further
							}
						}
					} //foreach of secondary_resources
					$imported_files = array(); //reset the imported file for the next resource 
				}				
			} //foreach of resources
		} //foreach of item array
	}

	/**
	 * By the given attrs array, decide which resource type it is
	 *	auditory		= type 1
	 *  sign_language	= type 2
	 *	textual			= type 3
	 *	visual			= type 4
	 * @param	array
	 * return type id array
	 */
	 function toResourceTypeId($resources_attrs){
		 $result = array();

		 //if empty
		 if (empty($resources_attrs)){
			 return $result;
		 }
		if (is_array($resources_attrs)) {
				 if (in_array('auditory', $resources_attrs)){
					 $result[] = 1;
				 }
				 if (in_array('sign_language', $resources_attrs)){
					 $result[] = 2;
				 }
				 if (in_array('textual', $resources_attrs)){
					 $result[] = 3;
				 }
				 if (in_array('visual', $resources_attrs)){
					 $result[] = 4;
				 }		
		} else {
			if ($resources_attrs=='auditory'){
				$result[] = 1;
			} elseif ($resources_attrs=='sign_language'){
				$result[] = 2;
			} elseif ($resources_attrs=='textual'){
				$result[] = 3;
			} elseif ($resources_attrs=='visual'){
				$result[] = 4;
			}
		}
		return $result;
	}
}

?>
