<?php

if (!defined('TR_INCLUDE_PATH')) exit;

class Page_template {
	/**
	 * Update an existing course record
	 * @access  public
	 * @param   courseID: course ID
	 *          fieldName: the name of the table field to update
	 *          fieldValue: the value to update
	 * @return  true if successful
	 *          error message array if failed; false if update db failed
	 * @author  Mauro Donadio
	 */

	/*
	 * Variables declarations / definitions
	 * 
	 * */

	private $mod_path	= array();
	private $config		= array();
	private $content_id	= '';
	private $course_id	= '';
	private $uniq		= 'templates';

	// folders and documents to be excluded from the list of the page_template
	private $except		= array('.', '..', '.DS_Store', 'desktop.ini', 'Thumbs.db');


	/**
	 * Constructor: sets the main variables used (paths, ..)
	 * @access  public
	 * @param   mod_path: associative array containing the paths list
	 * @return  none
	 * @author  Mauro Donadio
	 */

	public function __construct($mod_path){
		global $_course_id, $_content_id;

		/* content id of an optional chapter */
		$this->content_id	= (isset($_REQUEST['cid']) ? intval($_REQUEST['cid']) : $_content_id);
		$this->course_id	= (isset($_REQUEST['course_id']) ? intval($_REQUEST['course_id']) : $_course_id);

		//
		if(isset($_POST['cid'], $_POST['text'])){
			$this->applyPageTemplateToContent();
		}

		// test 30/10/2012
		if(isset($_POST['save_page_templates']))
		{
			$this->applyPageTemplateToContent();
		}

		$this->mod_path		= $mod_path;

		if($this->mod_path != '')
			$this->config		= parse_ini_file($this->mod_path['syspath'].'config.ini');

		return;
	}

           public function v(){
            ?>
<div class="input-form">
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" style="display: inline;">
        <li>
             <input type="submit" style="width:250px;" value="Back to options screen" id="template_back" name="template_back" />   
        </li>
    </form>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="test" method="post" style="display: inline;" onsubmit="return false">
    <link rel="stylesheet" type="text/css" href="<?php echo TR_BASE_HREF; ?>/themes/default/forms.css">
    <div style="text-align:left; margin: 10px; margin-top: 20px; margin-bottom: 15px;">

    <li id="deactivate_page_template_bar" style="display:<?php ($with_content == 0 ? 'none' : 'inline') ?>">
        <button type="button" id="deactivate_page_template" class="button"><?php echo _AT('hide_templates'); ?></button>
    </li>

    <li id="activate_page_template_bar" style="display: <?php ($with_content == 0 ? 'none' : 'inline') ?>">
            <button type="button" id="activate_page_template" class="button"><?php ($with_content == 0 ? _AT('show_templates') : _AT('add_templates'))?></button>

    <li id="orderPageTemplate_bar" style="display: none;">
        <div style="padding:5px;"></div>
            <button type="button" id="orderPageTemplate" class="button"><?php echo _AT('edit_templates'); ?></button>
    </li>
    <div style="padding:5px;"></div>
    <li id="savePageTemplate_bar" style="display: <?php ($with_content == 0 ? 'inline' : 'none') ?>">
        <input id="server_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>" type="hidden" />
        <input id="content_id" value="<?php echo $_content_id ?>" type="hidden" />
        <button type="button" id="savePageTemplate" class="button"><?php echo _AT('save');?></button>
    </li>
    </div>

    <script type="text/javascript" src="<?php echo TR_BASE_HREF; ?>templates/system/Page_template.js"></script>

    <?php
    $with_content=1;
    $pageTemplateList = array();
// Db calls to get the values ??of the structure and title
    define('TR_INCLUDE_PATH', '../../include/');
    include_once(TR_INCLUDE_PATH.'classes/DAO/DAO.class.php');
    require_once(TR_INCLUDE_PATH.'lib/tinymce.inc.php');
    require_once(TR_INCLUDE_PATH.'classes/FileUtility.class.php');
    require_once(TR_INCLUDE_PATH.'../home/classes/StructureManager.class.php');
    require_once(TR_INCLUDE_PATH.'vitals.inc.php');
require_once(TR_INCLUDE_PATH.'classes/DAO/ContentDAO.class.php');
require_once(TR_INCLUDE_PATH.'classes/DAO/PrivilegesDAO.class.php');
require_once(TR_INCLUDE_PATH.'classes/DAO/CoursesDAO.class.php');

    Utility::authenticate(TR_PRIV_ISAUTHOR);
    
    $dao = new DAO();

		$sql="SELECT structure FROM ".TABLE_PREFIX."content WHERE content_id=".$this->content_id."";
		$result=$dao->execute($sql);

		if(is_array($result))
		{
			foreach ($result as $support) {
				$content=$support['structure'];
				break;
			}
		}
		$sql="SELECT title FROM ".TABLE_PREFIX."content WHERE content_id=".$this->content_id."";
		$result=$dao->execute($sql);
		if(is_array($result)) {
			foreach ($result as $support) {
				$title=$support['title'];
				break;
			}
		}
    
//die($title); ok!!!
//die($content); ok!!!
    
    if($content!='') {
            // Upload the array of default page template structure
            $structManager = new StructureManager($content);

            $item=$structManager->getPageTemplatesItem($title);
            $array = $structManager->getContent($item);

            //	$pageTemplateList = $this->validatedPageTemplate($array);
    }

    $pageTemplateList = $this->getPageTemplateList();
    
//die($pageTemplateList); ARRAY

    ?>
    <link rel="stylesheet" href="<?php echo TR_BASE_HREF; ?>templates/system/page_template.css" type="text/css" />
    <?php
    if($pageTemplateList != null){
   //     die("nn nullo");
    ?>
        <div class="boxTotal" style="display: <?php ($with_content == 0 ? "block" : "none") ?>">
        <div class="boxPageTemplate" style="display:block;">
        <ul>
    <?php
        foreach ($pageTemplateList as $key => $value) {
    ?>
        <li>
        <table id="<?php echo $key ?>" >
        <tr>
        <td>
        <a href="javascript: void(0);">
        <img title="<?php echo _AT('img_title_pagetemplate_icon', $value['name']) ?>" style="padding:10px;" src="<?php echo TR_BASE_HREF; ?>templates/page_template/<?php echo $key; ?>/screenshot.png" alt="<?php echo _AT('img_pagetemplate_icon',$key); ?>" /><br />
        <span class="desc"><?php echo $value['name']; ?></span>
        </a>
        </td>
        </tr>
        </table>
        </li>
    <?php
        }
    ?>
        </ul>
        </div> 
        <div class="boxPageTemplateTool">
        <ul>
        </ul>
        <ul>
        <li id="pageTemplatePaste" style="display: none;">
        <img alt="error paste" title="paste" src="<?php echo TR_BASE_HREF; ?>templates/system/paste.png">
<?php        echo _AT('paste_page_template'); ?>
        </li>
        <li id="pageTemplateCopy">
        <img alt="error copy" title="copy" src="<?php echo TR_BASE_HREF; ?>templates/system/copy.png">
        <?php echo _AT('copy_page_template'); ?>
        </li>
        </ul>
        </div>
        </div> 

        <div id="content-text"></div>
    <?php    
    }
    ?>	
    </form>
</div>
<?php
return;
        }
        
	public function view_page_templates($with_content)
	{            
		// 
		// form if using save as button
		echo '<form action="'.$_SERVER['REQUEST_URI'].'" id="test" method="post" style="display: none;" onsubmit="return false">';

		// added css for labels that become buttons
		echo '<link rel="stylesheet" type="text/css" href="'.TR_BASE_HREF.'/themes/default/forms.css">';

		echo '<div style="text-align:left; margin: 10px; margin-top: 20px; margin-bottom: 15px;">';

		if ($with_content == 0) {
			echo '<span>'._AT("note_at_first_access").'</span><br />';
		}
		echo '<li id="deactivate_page_template_bar" style="display: '. ($with_content == 0 ? 'inline' : 'none') . ';">';
		echo '<button type="button" id="deactivate_page_template" class="button">'. _AT('hide_templates') .'</button>';
		echo '</li>';

		echo '<li id="activate_page_template_bar" style="display: '.($with_content == 0 ? 'none' : 'inline').';">';
		echo '<button type="button" id="activate_page_template" class="button">'.($with_content == 0 ? _AT('show_templates') : _AT('add_templates')).'</button>';

		// display the button if there is only arrange content
		echo '<li id="orderPageTemplate_bar" style="display: none;">';
		echo '<div style="padding:5px;"></div>';
		echo '<button type="button" id="orderPageTemplate" class="button">'._AT('edit_templates').'</button>';
		echo '</li>';

		echo '<div style="padding:5px;"></div>';

		//  code for save (LABEL by DEFAULT /// this also code for the button)
		echo '<li id="savePageTemplate_bar" style="display: '. ($with_content == 0 ? 'inline' : 'none') .'">';
		echo '<input id="server_url" value="'.$_SERVER['REQUEST_URI'].'" type="hidden" />';
		echo '<input id="content_id" value="'.$this->content_id.'" type="hidden" />';
		echo '<button type="button" id="savePageTemplate" class="button">'._AT('save').'</button>';
		// BUTTON   echo '<input type="submit" value="save" id="save_page_templates" name="save_page_templates" />';
		echo '</li>';
		echo '</div>';

		echo '<script type="text/javascript" src="'.TR_BASE_HREF.'templates/system/Page_template.js"></script>';

		$pageTemplateList = array();

		// Db calls to get the values ​​of the structure and title
		define('TR_INCLUDE_PATH', '../../include/');
		include_once(TR_INCLUDE_PATH.'classes/DAO/DAO.class.php');
		require_once(TR_INCLUDE_PATH.'lib/tinymce.inc.php');
		require_once(TR_INCLUDE_PATH.'classes/FileUtility.class.php');
		require_once(TR_INCLUDE_PATH.'../home/classes/StructureManager.class.php');
		Utility::authenticate(TR_PRIV_ISAUTHOR);
		$dao = new DAO();

		$sql="SELECT structure FROM ".TABLE_PREFIX."content WHERE content_id=".$this->content_id."";
		$result=$dao->execute($sql);

		if(is_array($result))
		{
			foreach ($result as $support) {
				$content=$support['structure'];
				break;
			}
		}
		$sql="SELECT title FROM ".TABLE_PREFIX."content WHERE content_id=".$this->content_id."";
		$result=$dao->execute($sql);
		if(is_array($result)) {
			foreach ($result as $support) {
				$title=$support['title'];
				break;
			}
		}

		if($content!='') {
			// Upload the array of default page template structure
			$structManager = new StructureManager($content);

			$item=$structManager->getPageTemplatesItem($title);
			$array = $structManager->getContent($item);

			//	$pageTemplateList = $this->validatedPageTemplate($array);
		}

		$pageTemplateList = $this->getPageTemplateList();

		echo '<link rel="stylesheet" href="'.TR_BASE_HREF.'templates/system/page_template.css" type="text/css" />';
		// avoid the input when the array is empty
		if($pageTemplateList != null){

			echo '<div class="boxTotal" style="display: '. ($with_content == 0 ? "block" : "none").';">';
			echo '<div class="boxPageTemplate" style="display:block;">';
			echo '<ul>';
			foreach ($pageTemplateList as $key => $value) {
				//Check if there is a structure and search if the page template belongs
				// scanned array of predefined structure
				
//				if($content!=''){
//					if(in_array($key,$array)){
//						echo '<li>';
//						echo '<table id="'.$key.'" >';
//						echo '<tr>';
//						echo '<td>';
//						echo '<a href="javascript: void(0);">';
//						echo '<img title="'._AT('img_title_pagetemplate_icon',$value['name']).'" style="padding:10px;" src="'.TR_BASE_HREF.'/templates/page_template/'.$key.'/screenshot.png" alt="'._AT('img_pagetemplate_icon',$key).'" /><br />';
//						echo '<span class="desc">'.$value['name'].'</span>';
//						echo '</a>';
//						echo '</td>';
//						echo '</tr>';
//						echo '</table>';
//						echo '</li>';  
//					}
//				} else {
					echo '<li>';
					echo '<table id="'.$key.'" >';
					echo '<tr>';
					echo '<td>';
					echo '<a href="javascript: void(0);">';
					echo '<img title="'._AT('img_title_pagetemplate_icon', $value['name']).'" style="padding:10px;" src="'.TR_BASE_HREF.'templates/page_template/'.$key.'/screenshot.png" alt="'._AT('img_pagetemplate_icon',$key).'" /><br />';
					echo '<span class="desc">'. $value['name'] . '</span>';
					echo '</a>';
					echo '</td>';
					echo '</tr>';
					echo '</table>';
					echo '</li>';  
//				}
			}
			echo '</ul>';
			echo '</div>'; // div boxPageTemplate  
			// two button PASTE and COPY
			echo '<div class="boxPageTemplateTool">';
			echo '<ul>';
			echo '</ul>';

			echo '<ul>';
			echo '<li id="pageTemplatePaste" style="display: none;">';
			echo '<img alt="error paste" title="paste" src="'.TR_BASE_HREF.'templates/system/paste.png">';
			echo _AT('paste_page_template');
			echo '</li>';
			echo '<li id="pageTemplateCopy">';
			echo '<img alt="error copy" title="copy" src="'.TR_BASE_HREF.'templates/system/copy.png">';
			echo _AT('copy_page_template');
			echo '</li>';
			echo '</ul>';
			echo '</div>';
			echo '</div>'; // div boxTotal

			echo '<div id="content-text"></div>';
		}
		
		echo '</div>';
		echo '</form>';
	}

	/*
	 * Open the configuration file reading the parameters
	 * input:	none
	 * output:	none
	 * 
	 */

	public function getConfig(){
		return $this->config;
	}

	/*
	 * Read loaded themes creating a list of available themes
	 * input:	none
	 * output:	none
	 * 
	 * */

	public function getPageTemplateList(){
		$page_template_list	= array();
		$dir			= array();

		// read the list of available themes
		$dir		= scandir($this->mod_path['page_template_dir_int']);
                
		// subtract files to be excluded from the list of available themes
		$dir		= array_diff($dir, $this->except);

		$dir		= array_merge(array(),$dir);
                
		// call the function that validates the available themes
		$page_template_list	= $this->validatedPageTemplate($dir);

		return $page_template_list;
	}
        
	
	function getPageTemplates($item) {
		$pages = array();
		foreach ($item->children() as $child) {
			 $name = (string)$child['name'];
			 $pages[$name] = $this->checkPageTemplate($child['name']);
		}

		return $pages;
	}
	
	function checkPageTemplate($name) {
		$info = null;
		$isdir = $this->mod_path['page_template_dir_int'].$name;
		// checking if the element is a directory
		if(is_dir($isdir)){
			// check if exists the .info file and parse it
			$xml_file = $isdir.'/page_template.xml';
			if(is_file($xml_file)) {
				$xml = simplexml_load_file($xml_file);

				foreach($xml->children() as $child) {
					$name = $child->getName();
					if($name == "release") 
						$info['core'] = trim($child->version);
					else
						$info[$name] = trim($child);
				}
				
				// if you did not specify a name, use the folder name
				if(!$info['name'])
					$info['name'] = trim($item);
				
				// reduce the name length to 15 characters
				$limit	= 15;
				if(strlen($info['name']) >= $limit){
					$info['name']	= substr($info['name'], 0, ($limit-2));
					$info['name']	.= '..';
				}

				// check the "core"
				if(!$info['core'])
					continue;
				else{

					$vfile	= explode('.', $info['core']);
					$vcore	= explode('.', VERSION);
	
					// cursory check for version compatibility
					// stopping the cycle to the first incompatibility found
					/* WHAT IS THIS? Using continue here breaks
					if($vfile[0] < $vcore[0])
						// not compatible!
						continue;
					elseif(strtolower($vfile[1]) != 'x' AND $vfile[1] < $vcore[1])
						// not compatible!
						continue;
					*/
				}

				// put the info of the current model into an array
				//$modelli[$item] = $info;
			}
		}	
		return $info;
	}
	
	/*
	 * 	The following function reads from the filesystem existing page_template and validates them
	 * 	according to pre-set criteria (eg comparison between version of the model and core)
	 * 	and returns an array of available and valid page_template.
	 *	input:	$dir[]			list of available page_template
	 * 	output:	list of available page_template skimmed according to the compatibility of each model
	 * 
	 * */
	
	function validatedPageTemplate($dir = array()){
            
		// scan all existing themes
		$page_template = array();
		foreach($dir as $item)  {
			$page_template[$item] = $this->checkPageTemplate($item);
		}

		return $page_template;
	}

	private function applyPageTemplateToContent() {
		require_once(TR_INCLUDE_PATH.'classes/DAO/ContentDAO.class.php');
		
		if(isset($_POST['cid'])) echo $_POST['body_text'];
		
		$cid	= htmlentities($_POST['_cid']);
		$text	= $this->textFixPHP($_POST['body_text']);
		echo $cid;
		echo $text;

		if(strlen($text) == 0) return;

		$contentDAO = new ContentDAO();

		// write on db
		$contentDAO->UpdateField($cid, "text", $text);

		// page redirect
		/*echo '<script type="text/javascript">';
			echo 'window.location = "'.$_SERVER['REQUEST_URI'].'";';
		echo '</script>';*/

		return;
	}

	public function control(){}

	public function applyPageTemplate($cid,$text){
		global $stripslashes;
		
		require_once(TR_INCLUDE_PATH.'classes/DAO/ContentDAO.class.php');

		$contentDAO = new ContentDAO();

		// write on db
		$contentDAO->UpdateField($cid, "text", $stripslashes($text));
		
		return;
	}

	public function getpage_templatetructure($pageTemplateID = ''){
		$struct	= '';

		$file = '../../templates/page_template/'.$pageTemplateID.'/'.$pageTemplateID.'.html';

		if(file_exists($file)) {
			$struct = file_get_contents($file);
		}

		return $struct;
	}

	/*
	 *	exaggeration
	 *	TinyMCE is not precise with the carriage return, then, I try to repair
	 *	the display differences between TinyMCE and AContent preview.
	 *	text	= text to clean up
	*/

	private function textFixPHP($text = ''){
	
		return $text;
	}
}
?>
<script>
$('.unsaved').css('display','none');
</script>