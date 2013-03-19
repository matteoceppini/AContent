<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<?php
    if (!defined('TR_INCLUDE_PATH')) { exit; } 
    global $onload;
    $onload = 'document.form.title.focus();';
    require_once(TR_INCLUDE_PATH.'classes/CoursesUtility.class.php');
    require_once(TR_INCLUDE_PATH.'../home/classes/GoalsManager.class.php');

    global $_course_id;

    $contentDAO = new ContentDAO();
    define("PATH", "../../include/");
    require_once(PATH.'classes/DAO/ContentDAO.class.php');
    
    $sql="SELECT structure FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id."";
    $result=$contentDAO->execute($sql);
    if(is_array($result))
    {
        foreach ($result as $support) {
           $structure=$support['structure']; //ok
           break;
        }  
    }

?>

<div class="input-form" style="width:95%; margin-left:1.5em;">
    <div class="row">
		<div style="font-weight:bold;"><span class="required" title="<?php echo _AT('required_field'); ?>"></span><label for="ftitle">Here you can view the predefined AContent that offers to create lessons.</label></div>

		<?php  
			
			$mod_path['templates']		= realpath(TR_BASE_HREF			. 'templates').'/';
			$mod_path['templates_int']	= realpath(TR_INCLUDE_PATH		. '../templates').'/';
			$mod_path['templates_sys']	= $mod_path['templates_int']	. 'system/';
			$mod_path['structs_dir']		= $mod_path['templates']		. 'structures/';
			$mod_path['structs_dir_int']	= $mod_path['templates_int']	. 'structures/';

			include_once($mod_path['templates_sys'].'Structures.class.php');
			
			$structs	= new Structures($mod_path);

			$structsList = $structs->getStructsList();
			if (!is_array($structsList)) {
					$num_of_structs = 0;
					$output = _AT('none_found');
			} else {
                            echo '<div style=" weight: 10%; margin: 10px;">';
                            foreach ($structsList as $struct) {
                            //	echo '<input type="radio" name="title" id="'.$struct['name'].'" class="formfield" value="'.$struct['short_name'].'"/>';
                                $supp=strtoupper($struct['name']);
                                echo '<label for="'.$struct['name'].'"><b>'.$supp.'</b></label>';
                                $value = "";
                                foreach ($structsList as $val) { 
                                    if(isset($_POST['struct']) && $_POST['struct'] == $val['short_name'])
                                            $check = true;
                                    else 
                                            $check = false;

                                    if($val['name'] == $struct['name']){
                  ?>                      
                                        <div style=" margin-bottom: 10px; <?php if($check) echo 'border: 2px #cccccc dotted;';?> ">
                                <!--	<li id="<?php echo $val['short_name'];?>"> <?php echo $val['name'];?> </li> -->

                                        <p style="margin-left: 10px; font-size:90%;"><span style="font-style:italic;"><?php echo _AT('description'); ?>:</span>
                                        <?php //echo $val['description']; 
                                            if($val['short_name']=='creative'){
                                                echo 'centered on the motivations and "emotions" of the learner.';
                                            }
                                            if($val['short_name']=='meta-competency'){
                                                echo 'emphasizes the logic of a constructivist approach to the construction of knowledge.';
                                            }
                                            if($val['short_name']=='knowledge'){
                                                echo 'is to follow a path of self-learning to acquire the "base" of the subject matter.';
                                            }
                                        
                                        
                                        ?></p>
                                        <div style="font-size:95%; margin-left: 10px;">
                                                <a title="outline_collapsed" id="a_outline_<?php echo $val['short_name'];?>" onclick="javascript: trans.utility.toggleOutline('<?php echo $val['short_name'];?>', '<?php echo _AT('hide_outline'); ?>', '<?php echo _AT('show_outline'); ?>'); " href="javascript:void(0)"><?php echo _AT('show_outline'); ?></a>
                                                <div style="display: none;" id="div_outline_<?php echo $val['short_name'];?>">
                                                        <?php $struc_manag = new StructureManager($val['short_name']);
                                                        $struc_manag->printPreview(false, $val['short_name']); ?>
                                                </div>
                                        </div>
                                        </div>

                                        <?php 
                                       }
                                }
		echo '</br>';
                            }
                echo '</div>';          
			}
		?>	
    </div>
</div>