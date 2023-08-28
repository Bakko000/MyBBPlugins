<?php

/**
* Private Threads plugin (/inc/plugins/privatethreads.php)
*
* @author Surge&The_Dark Corp
* @version 1.0
* @copyright  2015 © Surge&The_Dark
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
	$templatelist .= 'privatethreads_field';
}




$plugins->add_hook("usercp_options_start", "privatethreads_optionstart");
$plugins->add_hook("usercp_do_options_end", "privatethreads_optionsend");
$plugins->add_hook("showthread_start", "privatethreads_showthread");



function privatethreads_info() {

 global $lang, $db, $mybb;

 $lang->load("privatethreads");


            return array(


               "name"          =>   $lang->privatethreads_info_name,
			   "description"   =>   $lang->privatethreads_info_desc,
			   "website"       =>   "http://yugiohspirits.altervista.org/",
			   "author"        =>   "The_Dark",
			   "authorsite"    =>   "http://yugiohspirits.altervista.org/",
			   "version"       =>   "1.0",
			   "compatibility" =>   "18*"

			   	);

        }




     function privatethreads_install() {

     	global $db, $mybb, $lang, $templates;

     	$lang->load("privatethreads");


	$template = '<legend><strong>{$lang->pt_private_threads}</strong></legend>
<table cellspacing="0" cellpadding="2">
<tr>
<td colspan="2"><span class="smalltext">{$lang->pt_who_can_view}</span></td>
</tr>
<tr>
<td colspan="2">
<select name="whocanviews" id="whocanviews">
<option value="0" {$everyonecan}>{$lang->pt_who_can_view_everyone}</option>
<option value="1" {$nobodycan}>{$lang->pt_who_can_view_no_one}</option>
<option value="2" {$onlybuddycan}>{$lang->pt_who_can_view_only_buddy}</option>
<option value="3" {$onlymemberscan}>{$lang->pt_who_can_view_only_members}</option>
<option value="4" {$onlyguestcan}>{$lang->pt_who_can_view_only_guest}</option>
</td>
</tr>
<tr>
<td class="trow2" align="left">
<div><span class="smalltext">Enter your threads id (tid) affected by this restrictions (sepaereted by comma)</span></div>
<input type="text" class="textbox" name="threadsaffected" value="{$threadsaffected}" size="40" maxlength="250" />
</td>
</tr>
</table>
</fieldset>
<br />
<fieldset class="trow2">';



	$template_array = array(
		'title'		=> 'privatethreads_field',
		'template'	=> $db->escape_string($template),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> time()
	);

$db->insert_query("templates", $template_array);

        if(! $db->field_exists("whocanviews", "users"))  {
		 $db->add_column("users", "whocanviews", "int(10) NOT NULL default '0'"); // 0=everyone can view my thread, 1= no one can, 2=only my buddy list can, 3=only members of this board can, 4=only guests can
		}

		if(! $db->field_exists("threadsaffected", "users"))  {
        $db->add_column("users", "threadsaffected", "TEXT NOT NULL"); 


		}
		





     	$settinggroups = array(

        'name'			=> 'privatethreads', 
		'title'			=> $lang->privatethreads_settinggroups_title, 
		'description'	=> $lang->privatethreads_settinggroups_desc, 
		'disporder'		=> 999

     		);

     	$gid = $db->insert_query("settinggroups", $settinggroups);



     	$setting= array(
		"name"			=> "privatethreads_usergroup",
		"title"			=> $lang->privatethreads_settings_usergroup,
		"description"	=> $lang->privatethreads_settings_usergroup_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 1,
		"gid"			=> intval($gid)
	);


  $db->insert_query("settings", $setting);


    	rebuild_settings();

}



function privatethreads_is_installed() {

	global $db;
    
	if($db->field_exists("whocanviews", "users") && ($db->field_exists("threadsaffected", "users"))) {
		return true;
	}
	return false;
}




function privatethreads_uninstall() {

	global $db;

	
    if($db->field_exists("whocanviews", "users"))  
    { $db->drop_column("users", "whocanviews"); }

  if($db->field_exists("threadsaffected", "users"))
  { $db->drop_column("users", "threadsaffected"); }	

    $db->delete_query("templates", "title = 'privatethreads_field'");

	$db->delete_query("settinggroups", "name = 'privatethreads'");

	$db->delete_query("settings", "name LIKE 'privatethreads_%'");

	rebuild_settings();
}


function privatethreads_activate() {

	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

find_replace_templatesets("usercp_options", '#' 
	. preg_quote('<legend><strong>{$lang->messaging_notification}</strong></legend>') . '#i',
 '{$my_threads}<legend><strong>{$lang->messaging_notification}</strong></legend>'
 );


}



function privatethreads_deactivate() {

	global $db;

require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

	find_replace_templatesets("usercp_options", '#' 
		. preg_quote('{$my_threads}') . '#i', '', 0);

}




function privatethreads_optionstart() {

	global $templates, $privatethreads_field, $my_threads, $mybb, $db, $lang;

	$lang->load("privatethreads");

$usergroupsfields = $mybb->settings['privatethreads_usergroup'];

	if(in_array($mybb->user['usergroup'], explode(',', $usergroupsfields))) {

 $everyonecan   = $mybb->user["whocanviews"] == "0" ? "selected=\"selected\"" : "";
 $nobodycan     = $mybb->user["whocanviews"] == "1" ? "selected=\"selected\"" : "";
 $onlybuddycan  = $mybb->user["whocanviews"] == "2" ? "selected=\"selected\"" : ""; 
 $onlymemberscan = $mybb->user["whocanviews"] == "3" ? "selected=\"selected\"" : "";
 $onlyguestcan = $mybb->user["whocanviews"] == "4" ? "selected=\"selected\"" : "";
 $threadsaffected = $db->escape_string($mybb->input['threadsaffected']);



eval("\$my_threads = \"".$templates->get("privatethreads_field")."\";");

	}

}





function privatethreads_optionsend() {

global $db, $mybb;


    if(isset($mybb->input["whocanviews"]) && $mybb->user["whocanviews"] == $mybb->input["whocanviews"]
	&& isset($mybb->input["threadsaffected"]) && $mybb->user["threadsaffected"] == $mybb->input["threadsaffected"])
	 {
				return;
		}
		
		$update = array();

	if(in_array($mybb->input["whocanviews"], array("0", "1", "2", "3", "4", "5"))) {
			$update["whocanviews"] = $mybb->input["whocanviews"];

		    $threadsaffected = $db->escape_string($mybb->input["threadsaffected"]);

		    $db->update_query("users", array("threadsaffected" => $threadsaffected), "uid=".$mybb->user['uid']."");

		}

		if(count($update) > 0) {

            
			$db->update_query("users", $update, "uid='{$mybb->user['uid']}'", "1");

			

			
		}
     }








function privatethreads_showthread($tid) {


global $lang, $mybb, $db, $tid, $thread;

$lang->load("privatethreads");

$query= $db->simple_select("threads", "uid", "tid=".$tid."");

while($authorthread = $db->fetch_field($query, "uid")) {

if(! in_array($mybb->user['uid'], explode(",", $authorthread))) /* don't use features about plugin if the current user is the author of current thread :D */

	{

$query= $db->simple_select("users", "threadsaffected", "uid=".$authorthread."");

while($textbox = $db->fetch_array($query)) {
 
 if(in_array($tid, explode(",", $textbox['threadsaffected']))) {
 
 $query=$db->simple_select("users", "whocanviews", "uid=".$authorthread." AND whocanviews=1");    // nobody can view my threads

while($select1 = $db->fetch_array($query)) {

 if($mybb->user['uid']) {


error("Author of this thread has setted restrictions, which ban all users and guests from his thread");

 }

 if(! $mybb->user['uid']) {


error("Author of this thread has setted restrictions, which ban all users and guests from his thread");

 }

}



// only my buddylist can

$query=$db->simple_select("users", "whocanviews", "uid=".$authorthread." AND whocanviews=2");

while($select2 = $db->fetch_array($query)) {

  $query=$db->simple_select("users", "buddylist", "uid=".$authorthread."");

  while($isnotbuddy = $db->fetch_array($query)) {

      if(! in_array($mybb->user['uid'], explode(",", $isnotbuddy['buddylist']))) {


      	error("Author of this thread has setted restrictions, which ban all users from his thread, which aren't in your buddylist");
      }

  }

}



// only member of this board can view my thread

$query=$db->simple_select("users", "whocanviews", "uid=".$authorthread." AND whocanviews=3");

while($select3 = $db->fetch_array($query)) {

  if(! $mybb->user['uid']) {

  error("Author of this thread has setted restrictions, which ban all guests from his thread");

  }

}


// only guests can

$query=$db->simple_select("users", "whocanviews", "uid=".$authorthread." AND whocanviews=4");

while($select4=$db->fetch_array($query)) {
 
 if($mybb->user['uid']) {

 	error("Author of this thread has setted restrictions, which bann all users from his thread"); }}}}}}}
           










?>      