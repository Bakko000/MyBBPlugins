<?php

/**
 * Default Message plugin
 * Copyright = © 2014 The_Dark
 * Author = The_Dark & murder
 * Codding = The_Dark & murder
 * Version = 1.1
 * Special Thanks to murder
 * Website: http://yugiohspirits.altervista.org/
 * License: http://www.mybb.com/about/license
 */

if(!defined("IN_MYBB")) {
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("newreply_start", "default_message_newreply_start");
$plugins->add_hook("newthread_start", "default_message_newthread_start");


function default_message_info()
{
	return array(
		"name"			=> "Default Message",
		"description"	=> "Allows to the admins/moderators to write determinate text, the that show in specify forums BEFORE of the new thread or fast reply's send",
		"website"		=> "http://yugiohspirits.altervista.org/",
		"author"		=> "The_Dark",
		"authorsite"	=> "http://yugiohspirits.altervista.org/",
		"version"		=> "1.0",
		"compatibility"	=> "18*" 
	);
}


function default_message_install()
{
	
	global $db;


    $insertarray = array(
		'name' => 'defaultmessage',
		'title' => 'Default Message Setting',
		'description' => "Settings for Default Message",
		'disporder' => 6,
		'isdefault'  => 0
    );

	$gid = $db->insert_query("settinggroups", $insertarray);

	


		$setting = array(
		"name"			=> "defaultmessage_write",
		"title"			=> "Write Default Message",
		"description"	=> "Write the default message to show in chosed forums",
		"optionscode"	=> "textarea",
		"value"			=> "Some text",
		"disporder"		=> 2,
		"gid"			=> intval($gid)
    );

	$db->insert_query("settings", $setting);


	$setting = array(
		"name"			=> "defaultmessage_forum",
		"title"			=> "Forum",
		"description"	=> "Enter the forums id (seperated by a comma) where show the default message",
		"optionscode"	=> "text",
		"value"			=> 2,
		"disporder"		=> 1,
		"gid"			=> intval($gid)
	);

	$db->insert_query("settings", $setting);


	$setting = array(
		"name"			=> "defaultmessage_newthread",
		"title"			=> "Show in newthread page?",
		"description"	=> "Show defaul message in newthread page? Select yes or no",
		"optionscode"	=> "yesno",
		"value"			=> "0",
		"disporder"		=> 2,
		"gid"			=> intval($gid)
    );

	$db->insert_query("settings", $setting);

	$setting = array(
		"name"			=> "defaultmessage_newreply",
		"title"			=> "Show in newreply page?",
		"description"	=> "Show defaul message in newreply page? Select yes or no",
		"optionscode"	=> "yesno",
		"value"			=> "0",
		"disporder"		=> 2,
		"gid"			=> intval($gid)
    );

	$db->insert_query("settings", $setting);


	rebuild_settings();   
}


function default_message_is_installed()
{
	global $db;

	$query = $db->simple_select("settinggroups", "*", "name='defaultmessage'");
	$result = $db->fetch_array($query); 
	
	if(is_null($result)) { 
		return false; 
	}
	return true; 
}
	


function default_message_uninstall()
{
    global $db;

	
    $db->delete_query("settinggroups", "name = 'defaultmessage'");
    $db->delete_query("settings", "name IN ('defaultmessage_forum','defaultmessage_write')");

    rebuild_settings();
}


function default_message_activate()
{
	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

	
	find_replace_templatesets("newreply", "#" . preg_quote('{$message}') . "#i", '{$message}{$defaultmessage}');
	find_replace_templatesets("newthread", "#" . preg_quote('{$message}') . "#i", '{$message}{$defaultmessage}');
}


function default_message_deactivate()
{
	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

	
	find_replace_templatesets("newreply", "#" . preg_quote('{$defaultmessage}') . "#i", '');
	find_replace_templatesets("newthread", "#" . preg_quote('{$defaultmessage}') . "#i", '');
}



function default_message_newreply_start()

{
	
global $mybb, $fid, $defaultmessage;

if($mybb->settings['defaultmessage_newreply'] == "1")

	{

	$fids = $mybb->settings['defaultmessage_forum']; 
	$message = $mybb->settings['defaultmessage_write']; 

	if($message && in_array($fid, explode(',', $fids))) { 
		$defaultmessage = $message; 
	}

           }
}


function default_message_newthread_start() 
{
	global $mybb, $fid, $defaultmessage;

	if($mybb->settings['defaultmessage_newthread'] == "1")

	{

	$fids = $mybb->settings['defaultmessage_forum']; 
	$message = $mybb->settings['defaultmessage_write']; 

	if($message && in_array($fid, explode(',', $fids))) { 
		$defaultmessage = $message; 
	}

           }
}

?>