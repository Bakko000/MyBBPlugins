<?php


/**
* Private Profile plugin (/inc/plugins/privateprofile.php)
*
* @author Surge&The_Dark Corp
* @version 1.0
* @copyright  2014 © Surge&The_Dark
* 
*
                                                                                     http://yugiohspirits.altervista.org/
*
*
**/


if(!defined("IN_MYBB")) { exit(); }



if(my_strpos($_SERVER['PHP_SELF'], 'usercp.php'))
{
	global $templatelist;
	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	$templatelist .= 'privateprofile_field';
}




$plugins->add_hook("usercp_options_start", "privateprofile_optionstart");
$plugins->add_hook("usercp_do_options_end", "privateprofile_optionsend");
$plugins->add_hook("member_profile_end", "privateprofile_memberend");



function privateprofile_info() {

 global $lang, $db, $mybb;

 $lang->load("privateprofile");


            return array(


               "name"          =>   $lang->privateprofile_info_name,
			   "description"   =>   $lang->privateprofile_info_desc,
			   "website"       =>   "http://yugiohspirits.altervista.org/",
			   "author"        =>   "The_Dark",
			   "authorsite"    =>   "http://yugiohspirits.altervista.org/",
			   "version"       =>   "1.0",
			   "compatibility" =>   "18*"

			   	);

        }




     function privateprofile_install() {

     	global $db, $mybb, $lang, $templates;

     	$lang->load("privateprofile");


	$template = '<legend><strong>{$lang->pp_private_profile}</strong></legend>
<table cellspacing="0" cellpadding="2">
<tr>
<td colspan="2"><span class="smalltext">{$lang->pp_who_can_view}</span></td>
</tr>
<tr>
<td colspan="2">
<select name="whocanview" id="whocanview">
<option value="0" {$everyonecan}>{$lang->pp_who_can_view_everyone}</option>
<option value="1" {$nobodycan}>{$lang->pp_who_can_view_no_one}</option>
<option value="2" {$onlybuddycan}>{$lang->pp_who_can_view_only_buddy}</option>
<option value="3" {$onlymemberscan}>{$lang->pp_who_can_view_only_members}</option>
<option value="4" {$onlyguestcan}>{$lang->pp_who_can_view_only_guest}</option>
<option value="5" {$ifbanuser}>Ban users</option>
</td>
</tr>
<tr>
<td class="trow2" align="left">
<div><span class="smalltext">Enter user/s id/s (uid) to ban from your profile (separeted by comma)</span></div>
<input type="text" class="textbox" name="banuser" value="{$banuser}" size="40" maxlength="250" />
</td>
</tr>
</table>
</fieldset>
<br />
<fieldset class="trow2">';



	$template_array = array(
		'title'		=> 'privateprofile_field',
		'template'	=> $db->escape_string($template),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> time()
	);

$db->insert_query("templates", $template_array);

        if(! $db->field_exists("whocanview", "users"))  {
		 $db->add_column("users", "whocanview", "int(10) NOT NULL default '0'"); // 0=everyone can view my profile, 1= no one can, 2=only my buddy list can, 3=only members of this board can, 4=only guests can
		}

		if(! $db->field_exists("banuser", "users")) {
        $db->add_column('users', 'banuser', "TEXT NOT NULL");

		}
		





     	$settinggroups = array(

        'name'			=> 'privateprofile', 
		'title'			=> $lang->privateprofile_settinggroups_title, 
		'description'	=> $lang->privateprofile_settinggroups_desc, 
		'disporder'		=> 999

     		);

     	$gid = $db->insert_query("settinggroups", $settinggroups);



     	$setting= array(
		"name"			=> "privateprofile_usergroup",
		"title"			=> $lang->privateprofile_settings_usergroup,
		"description"	=> $lang->privateprofile_settings_usergroup_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 1,
		"gid"			=> intval($gid)
	);


  $db->insert_query("settings", $setting);


    	rebuild_settings();

}



function privateprofile_is_installed() {

	global $db;
    
	if($db->field_exists("whocanview", "users") && ($db->field_exists("banuser", "users"))) {
		return true;
	}
	return false;
}




function privateprofile_uninstall() {

	global $db;

	
    if($db->field_exists("whocanview", "users"))  
    { $db->drop_column("users", "whocanview"); }

  if($db->field_exists("banuser", "users"))
  { $db->drop_column("users", "banuser"); }

    $db->delete_query("templates", "title = 'privateprofile_field'");

	$db->delete_query("settinggroups", "name = 'privateprofile'");

	$db->delete_query("settings", "name LIKE 'privateprofile_%'");

	rebuild_settings();
}


function privateprofile_activate() {

	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

find_replace_templatesets("usercp_options", '#' 
	. preg_quote('<legend><strong>{$lang->messaging_notification}</strong></legend>') . '#i',
 '{$privateprofile_users}<legend><strong>{$lang->messaging_notification}</strong></legend>'
 );


}



function privateprofile_deactivate() {

	global $db;

require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

	find_replace_templatesets("usercp_options", '#' 
		. preg_quote('{$privateprofile_users}') . '#i', '', 0);

}




function privateprofile_optionstart() {

	global $templates, $privateprofile_field, $privateprofile_users, $mybb, $db, $lang;

	$lang->load("privateprofile");

$usergroupsfields = $mybb->settings['privateprofile_usergroup'];

	if(in_array($mybb->user['usergroup'], explode(',', $usergroupsfields))) {

 $everyonecan   = $mybb->user["whocanview"] == "0" ? "selected=\"selected\"" : "";
 $nobodycan     = $mybb->user["whocanview"] == "1" ? "selected=\"selected\"" : "";
 $onlybuddycan  = $mybb->user["whocanview"] == "2" ? "selected=\"selected\"" : ""; 
 $onlymemberscan = $mybb->user["whocanview"] == "3" ? "selected=\"selected\"" : "";
 $onlyguestcan = $mybb->user["whocanview"] == "4" ? "selected=\"selected\"" : "";
 $ifbanuser = $mybb->user["whocanview"] == "5" ? "selected=\"selected\"" : "";
 $banuser = $db->escape_string($mybb->input['banuser']);



eval("\$privateprofile_users = \"".$templates->get("privateprofile_field")."\";");

	}

}





function privateprofile_optionsend() {

global $db, $mybb;


	if(isset($mybb->input["whocanview"]) && $mybb->user["whocanview"] == $mybb->input["whocanview"]
	&& isset($mybb->input['banuser'])   && $mybb->user['banuser'] == $mybb->input['banuser'])
	 {
				return;
		}
		
		$update = array();

	if(in_array($mybb->input["whocanview"], array("0", "1", "2", "3", "4", "5"))) {
			$update["whocanview"] = $mybb->input["whocanview"];

			$banuser = $db->escape_string($mybb->input['banuser']);

		    $db->update_query("users", array("banuser" => $banuser), "uid=".$mybb->user['uid']."");

		}

		if(count($update) > 0) {

            
			$db->update_query("users", $update, "uid='{$mybb->user['uid']}'", "1");

			

			
		}
}




/**
* 
* Replace the common method GET with $mybb->input (default class of MyBB) :D
* 
* 
**/


function privateprofile_memberend($profileuid) {

global $db, $mybb, $lang, $cache, $user, $profileuid, $memprofile;

$lang->load("privateprofile"); 

$profileuid = intval($mybb->input['uid']); // get uid param (profile&uid=$profileuid) from link

if($mybb->user['uid'] != $profileuid)  {



	if($memprofile['whocanview'] == 1) {

		if($mybb->user['uid']) {

			error("This user has a private profile!");

		}

		if(! $mybb->user['uid']) {

			error("This user has a private profile!");
		}
	}

	// only member of this board

	if($memprofile['whocanview'] == 3) {

		if(! $mybb->user['uid']) {

			error("This user has a private profile!");
		}
	}

// only guests

if($memprofile['whocanview'] == 4) {

	if($mybb->user['uid']) {

		error("This user has a private profile!");
	}
}



// ban user

if($memprofile['whocanview'] == 5) {

$query=$db->simple_select("users", "banuser", "uid=".$profileuid."");

while($tag = $db->fetch_array($query)) {

if(in_array($mybb->user['uid'], explode(",", $tag['banuser']))) {

	error("This user has banned you from his profile!");
   }

   }
}


// only buddylist

$query=$db->simple_select("users", "*", "uid=".$profileuid." AND whocanview=2");

while($select = $db->fetch_array($query)) {

	if(! in_array($mybb->user['uid'], explode(",", $select['buddylist']))) {

		error("This user has a private profile!");

       }



	}

  }

} 

?>




