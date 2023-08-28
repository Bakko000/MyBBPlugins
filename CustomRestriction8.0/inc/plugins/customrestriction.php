<?php

/**
 * Custom Restriction plugin (The old Thread Restriction plugin)
 * Copyright = © 2014 
 * Author = The_Dark
 * Codding = The_Dark
 * Version = 8.0
 * Website: http://yugiohspirits.altervista.org/
 * License: http://www.mybb.com/about/license
*
*
* Intermediately release (6.1.2) Is now possible ban from pm function.
*
*  -  NEW  - Release 7.0 (possible set flood control to users in newthread pages) -  NEW  -
*
* Intermediately release 7.1.9 (correct bug about flooding system and add this restriction also in newreply page and option about it like forums)
*
*  - NEW - Release 7.2 (correct some important bug about threads and forums ban)
*
*  - NEW - Release 7.5 (correct some important bug about all plugin, and set ban from archive if the user viewing archive thread or archive forum where he is banned) 
* 
*   Intermediately release (Add ban from print thread)
*
*
*  - NEW - Release 8.0 (Add flood control also in new fast ajax reply)
*
*
 */


if(!defined("IN_MYBB")) { exit(); }

$plugins->add_hook("showthread_start", "customrestriction_showthread");
$plugins->add_hook("newthread_start", "customrestriction_newthread");
$plugins->add_hook("newthread_do_newthread_end", "customrestriction_donewthread");
$plugins->add_hook("editpost_end", "customrestriction_editpost");
$plugins->add_hook("editpost_do_editpost_end", "customrestriction_doeditpost");
$plugins->add_hook("forumdisplay_end", "customrestriction_forumend");
$plugins->add_hook("private_start", "customrestriction_private");
$plugins->add_hook("newthread_start", "customrestriction_floodnewthread");
$plugins->add_hook("newreply_start", "customrestriction_floodnewreply");
$plugins->add_hook("archive_thread_start", "customrestriction_archive_showthread");
$plugins->add_hook("printthread_end", "customrestriction_archive_showthread");
$plugins->add_hook("archive_forum_start", "customrestriction_archive_forumdisplay");
$plugins->add_hook("datahandler_post_insert_post", "customrestriction_ajax_ban_userfastreply");
$plugins->add_hook("misc_start", "customrestriction_redirectpage");



if(my_strpos($_SERVER['PHP_SELF'], 'newthread.php'))
{
	global $templatelist;
	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	$templatelist .= 'customrestriction_field';
}

if(my_strpos($_SERVER['PHP_SELF'], 'editpost.php'))
{
	global $templatelist;
	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	$templatelist .= 'customrestriction_field';
}

function customrestriction_info() {

	global $lang;

	$lang->load("customrestriction");

	return array(
		"name"			=> $lang->customrestriction_info_name,
		"description"	=> $lang->customrestriction_info_desc,
		"website"		=> "http://yugiohspirits.altervista.org/",
		"author"		=> "The_Dark",
		"authorsite"	=> "http://yugiohspirits.altervista.org/",
		"version"		=> "8.0",
		"compatibility"	=> "18*" 
	);
}

function customrestriction_install() {

	global $db, $lang, $towait;

	$lang->load("customrestriction");

	$db->add_column('threads', 'customrestriction_users', "TEXT NOT NULL");




	$template1 = '<html>
<head>
<title>{$mybb->settings[bbname]}</title>
{$headerinclude}
</head>
<body>
{$header}

<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<thead>
<tr>
<td class="thead">Error!</td>
</tr>
</thead>
<tbody>
<tr>
<td class="trow1"><p>Sorry, but the follow error occurs:</p>
    <p><strong>You must wait 1 or more seconds for posting once more!</strong></p></td>
</tr>
</tbody>
</table>
{$footer}
</body>
</html>';

$insert_array = array(
    'title' => 'customrestriction_error_template',
    'template' => $db->escape_string($template1),
    'sid' => '-1',
    'version' => '',
    'dateline' => time()
);

$db->insert_query('templates', $insert_array);




	$template = '<tr>
<td class="trow2"><strong>Enter the users uid banned from this thread(leave blank for disable)</strong></td>
<td class="trow2"><input type="text" class="textbox" name="customrestriction_users" size="40" value="{$customrestriction_users}" tabindex="1" /></td>
</tr>';

	$template_array = array(
		'title'		=> 'customrestriction_field',
		'template'	=> $db->escape_string($template),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> time()
	);

	$db->insert_query("templates", $template_array);

	$insertarray = array(
		'name'			=> 'customrestriction', 
		'title'			=> $lang->customrestriction_settinggroups_title, 
		'description'	=> $lang->customrestriction_settinggroups_desc, 
		'disporder'		=> 999,
	);

	$gid = $db->insert_query("settinggroups", $insertarray);







	$setting= array(
		"name"			=> "customrestriction_forum",
		"title"			=> $lang->customrestriction_settings_forum,
		"description"	=> $lang->customrestriction_settings_forum_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 1,
		"gid"			=> intval($gid)
	);


  $db->insert_query("settings", $setting);










$setting= array(
		"name"			=> "customrestriction_forumsetfloodctrl",
		"title"			=> $lang->customrestriction_settings_forumsetfloodctrl,
		"description"	=> $lang->customrestriction_settings_forumsetfloodctrl_desc,
		"optionscode"	=> "yesno",
		"value"			=> "0",
		"disporder"		=> 2,
		"gid"			=> intval($gid)
	);


  $db->insert_query("settings", $setting);









$setting = array(
		"name"			=> "customrestriction_forumsetfloodseconds",
		"title"			=> $lang->customrestriction_settings_forumsetfloodseconds,
		"description"	=> $lang->customrestriction_settings_forumsetfloodseconds_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 3,
		"gid"			=> intval($gid)
	);

	$db->insert_query("settings", $setting);






  $setting= array(
		"name"			=> "customrestriction_forumsetfloodusr",
		"title"			=> $lang->customrestriction_settings_forumsetfloodusr,
		"description"	=> $lang->customrestriction_settings_forumsetfloodusr_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 4,
		"gid"			=> intval($gid)
	);


  $db->insert_query("settings", $setting);










	$setting = array(
		"name"			=> "customrestriction_forumbanuser",
		"title"			=> $lang->customrestriction_settings_forumbanuser,
		"description"	=> $lang->customrestriction_settings_forumbanuser_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 5,
		"gid"			=> intval($gid)
	);

	$db->insert_query("settings", $setting);







    
	$setting = array(
		"name"			=> "customrestriction_forumbangroups",
		"title"			=> $lang->customrestriction_settings_forumbangroups,
		"description"	=> $lang->customrestriction_settings_forumbangroups_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 6,
		"gid"			=> intval($gid)
	);

	$db->insert_query("settings", $setting);








	$setting= array(
		"name"			=> "customrestriction_usergroup",
		"title"			=> $lang->customrestriction_settings_usergroup,
		"description"	=> $lang->customrestriction_settings_usergroup_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 7,
		"gid"			=> intval($gid)
	);

	$db->insert_query("settings", $setting);








	$setting = array(
		"name"			=> "customrestriction_threads",
		"title"			=> $lang->customrestriction_settings_threads,
		"description"	=> $lang->customrestriction_settings_threads_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 8,
		"gid"			=> intval($gid)
    );

	$db->insert_query("settings", $setting);













	$setting = array(
		"name"			=> "customrestriction_banuser",
		"title"			=> $lang->customrestriction_settings_banuser,
		"description"	=> $lang->customrestriction_settings_banuser_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 9,
		"gid"			=> intval($gid)
	);

	$db->insert_query("settings", $setting);









    
	$setting = array(
		"name"			=> "customrestriction_bangroups",
		"title"			=> $lang->customrestriction_settings_bangroups,
		"description"	=> $lang->customrestriction_settings_bangroups_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 10,
		"gid"			=> intval($gid)
	);

	$db->insert_query("settings", $setting);









	$setting = array(
		"name"			=> "customrestriction_pmbanuser",
		"title"			=> $lang->customrestriction_settings_pmbanuser,
		"description"	=> $lang->customrestriction_settings_pmbanuser_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 11,
		"gid"			=> intval($gid)
	);

	$db->insert_query("settings", $setting);









    
	$setting = array(
		"name"			=> "customrestriction_pmbangroups",
		"title"			=> $lang->customrestriction_settings_pmbangroups,
		"description"	=> $lang->customrestriction_settings_pmbangroups_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 12,
		"gid"			=> intval($gid)
	);

	$db->insert_query("settings", $setting);


	
	rebuild_settings();
}



function customrestriction_is_installed() {

	global $db;
    
	if($db->field_exists("customrestriction_users", "threads")) {
		return true;
	}
	return false;
}

function customrestriction_uninstall() {

	global $db;

	$db->drop_column('threads', 'customrestriction_users'); 
	$db->delete_query("templates", "title = 'customrestriction_field'");
	$db->delete_query("settinggroups", "name = 'customrestriction'");
	$db->delete_query("settings", "name LIKE 'customrestriction_%'");

	rebuild_settings();
}

function customrestriction_activate() {

	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

	find_replace_templatesets(
		"newthread",
		"#" . preg_quote('{$posticons}') . "#i",
		'{$posticons}{$customrestriction_field}'
	);

	find_replace_templatesets(
		"editpost",
		"#" . preg_quote('{$posticons}') . "#i",
		'{$posticons}{$customrestriction_field}'
	);
}

function customrestriction_deactivate() {  

	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

	find_replace_templatesets(
		"newthread",
		"#" . preg_quote('{$customrestriction_field}') . "#i",
		''
	);

	find_replace_templatesets(
		"editpost",
		"#" . preg_quote('{$customrestriction_field}') . "#i",
		''
	);
}

function customrestriction_newthread() {

	global $mybb, $templates, $customrestriction_field, $db;

	if(is_member($mybb->settings['customrestriction_usergroup'])) {

       $uidsr = $db->escape_string($mybb->input['customrestriction_users']);

		eval('$customrestriction_field  = "' . $templates->get('customrestriction_field') . '";');
	}
}

function customrestriction_donewthread() {

	global $db, $mybb, $tid;

		$uidsr = $db->escape_string($mybb->input['customrestriction_users']);
		$db->update_query("threads", array("customrestriction_users" => $uidsr), "tid='{$tid}'");
}

function customrestriction_editpost() {

	global $mybb, $thread, $postrow, $templates, $customrestriction_field;
	
	$pid = $mybb->get_input('pid', MyBB::INPUT_INT);

	if(is_member($mybb->settings['customrestriction_usergroup']) && $thread['firstpost'] == $pid) {

            $uidsr = $db->escape_string($mybb->input['customrestriction_users']);

		eval('$customrestriction_field  = "' . $templates->get('customrestriction_field') . '";');
	}
}

function customrestriction_doeditpost() {

	global $db, $mybb, $tid;

		$uidsr = $db->escape_string($mybb->input['customrestriction_users']);
		$db->update_query("threads", array("customrestriction_users" => $uidsr), "tid='{$tid}'");
}

function customrestriction_showthread() {

	global $mybb, $thread, $tid, $lang;

	$lang->load("customrestriction");
	
	$tids = $mybb->settings['customrestriction_threads'];
	$uids = $mybb->settings['customrestriction_banuser'];
	$ugroups = $mybb->settings['customrestriction_bangroups'];
	$uidsfield = explode(",", $thread['customrestriction_users']);

	if($mybb->user['uid']) {

	
	if(in_array($tid, explode(',', $tids)) && in_array($mybb->user['uid'], explode(',', $uids))) {
		error("The administrator has banned you from this thread, please, for information, contact him");
	}

	if(in_array($tid, explode(',', $tids)) && in_array($mybb->user['usergroup'], explode(',', $ugroups))) {
		error("The administrator has banned your usergroup from this thread, please, for information, contact him");
	}

	if(in_array($mybb->user['uid'], $uidsfield)) {
		error("The author, has banned you from this thread, please, for information, contact him");

	
	}

	}

}



function customrestriction_archive_showthread() {

	global $mybb, $thread, $tid, $fid, $action;

	$tids = $mybb->settings['customrestriction_threads'];
	$user_id = $mybb->settings['customrestriction_banuser'];
	$ugroups = $mybb->settings['customrestriction_bangroups'];

	if($mybb->input['action'] == "do_newreply" && $mybb->request_method == "post" || $mybb->input['ajax'])
		{
			$thread_id = $mybb->get_input('tid', 1);
		}
		elseif(isset($thread))
		{
			$thread_id = $thread['tid'];
		}
		else
		{
			$thread_id = "not set";
		}
		

             // for users


		if($user_id != "" && ($tids != ""))
		{	
			
	if(stristr($tids, ",") == true)
			{
				$tids = explode(",",$tids);
				$t_id = array_search($thread_id, $tids);
				if(is_numeric($t_id))
				{
					$tids = $tids[$t_id];
				}
				else
				{
					$tids = "not set";
				}
			}
			if(stristr($user_id, ",") == TRUE)
			{
				$user_id = explode("," , $user_id);
				$u_id = array_search($mybb->user['uid'], $user_id);
				if(is_numeric($u_id))
				{
					$user_id = $user_id[$u_id];
				}
				else
				{
					$user_id = "not set";
				}
			}

			if($user_id == $mybb->user['uid'])
			{
				if($tids == $thread_id)
				{ 
					if($action == "thread")
					{
						echo "You don't have permission to access this page!";
						die(archive_footer());
					}

				} 
			} 
		}



		// for usergroups



if($ugroups != "" && ($tids != ""))
		{	
			
	if(stristr($tids, ",") == true)
			{
				$tids = explode(",",$tids);
				$t_id = array_search($thread_id, $tids);
				if(is_numeric($t_id))
				{
					$tids = $tids[$t_id];
				}
				else
				{
					$tids = "not set";
				}
			}
			if(stristr($ugroups, ",") == TRUE)
			{
				$ugroups = explode("," , $ugroups);
				$u_id = array_search($mybb->user['usergroup'], $ugroups);
				if(is_numeric($u_id))
				{
					$ugroups = $ugroups[$u_id];
				}
				else
				{
					$ugroups = "not set";
				}
			}

			if($ugroups == $mybb->user['usergroup'])
			{
				if($tids == $thread_id)
				{ 
					if($action == "thread")
					{
						echo "You don't have permission to access this page!";
						die(archive_footer());
					}

				} 
			} 
		}


	}
		  





function customrestriction_forumend()

{

global $mybb, $forum, $forums, $fid, $lang;

	$lang->load("customrestriction");

	$fids = $mybb->settings['customrestriction_forum'];
	$uidss = $mybb->settings['customrestriction_forumbanuser'];
	$ugroupss = $mybb->settings['customrestriction_forumbangroups'];

      if($mybb->user['uid']) {


	if(in_array($fid, explode(',', $fids)) && in_array($mybb->user['uid'], explode(',', $uidss))) {

		error("The administrator has banned you from this forum, please, for information, contact him");
	}

if(in_array($fid, explode(',', $fids)) && in_array($mybb->user['usergroup'], explode(',', $ugroupss))) {

	error("The administrator has banned your usergroup from this forum, please, for information, contact him");

}

}

}




function customrestriction_archive_forumdisplay() {

   global $mybb, $fid, $plugins, $threadcache, $forum; 

   if(!$fid && isset($forum))
   {
	$fid = $forum['fid'];
	$archive = 1;
   }
    $fids = $mybb->settings['customrestriction_forum'];
	$uidss = $mybb->settings['customrestriction_forumbanuser'];
	$ugroupss = $mybb->settings['customrestriction_forumbangroups'];


// for user
 
	if($uidss != "" && ($fids != ""))
	{	
			if(stristr($fids, ",") == true)
			{
				$fids = explode(",", $fids);
				$forumid = array_search($fid, $fids);
				if(is_numeric($forumid))
				{
					$fids = $fids[$forumid];
				}
				else
				{
					$fids = "not set";	
				}
			}

			if(stristr($uidss, ",") == TRUE)
			{
				$uidss = explode("," , $uidss);
				$userid = array_search($mybb->user['uid'], $uidss);
				if(isset($userid))
				{
					$uidss = $uidss[$userid];
				}
				else
				{
					$uidss = "";
				}
			}
	

			if($uidss == $mybb->user['uid'])
			{
					
				if($fid == $fids)
				{
					if($archive == 1)
					{
						echo "You don't have permission to access this page!";
						die(archive_footer());
					}
					
				}


			}

		}	


			// for usergroup 


			if($ugroupss != "" && ($fids != ""))
	{	
			if(stristr($fids, ",") == true)
			{
				$fids = explode(",", $fids);
				$forumid = array_search($fid, $fids);
				if(is_numeric($forumid))
				{
					$fids = $fids[$forumid];
				}
				else
				{
					$fids = "not set";	
				}
			}

			if(stristr($ugroupss, ",") == TRUE)
			{
				$ugroupss = explode("," , $ugroupss);
				$usergroup_id = array_search($mybb->user['usergroup'], $ugroupss);
				if(isset($usergroup_id))
				{
					$ugroupss = $ugroupss[$usergroup_id];
				}
				else
				{
					$ugroupss = "";
				}
			}
	

			if($ugroupss == $mybb->user['usergroup'])
			{
					
				if($fid == $fids)
				{
					if($archive == 1)
					{
						echo "You don't have permission to access this page!";
						die(archive_footer());
					}
					
				}


			}

		}

    }



function customrestriction_private()

{

global $mybb, $user, $users, $user, $lang, $db, $private;



    require_once "./private.php";

	$lang->load("customrestriction");

    $userexist = $mybb->user['uid'];
	$ugp = $mybb->user['usergroup'];
    $checkusrs = $mybb->settings['customrestriction_pmbanuser'];
	$checkusergrps = $mybb->settings['customrestriction_pmbangroups'];


	if(in_array($userexist, explode(',', $checkusrs)))  {

		error("The administrator has banned you from pm's functions, please, for information, contact him");


	}


if(in_array($ugp, explode(',', $checkusergrps)))  {

		error("The administrator has banned your usergroup pm's functions, please, for information, contact him");

}

  }


	

	
    /**
	* 
	* Flood control in newthread.
	*
	*/
    function customrestriction_floodnewthread()  {
	global $db, $forum, $floodingsystem, $mybb, $fid, $forums, $fids, $uid, $uids, $usergroup, $user, $datahandler, $datahandlers, $post, $posts, $time;
	
	require_once MYBB_ROOT."/inc/datahandlers/post.php";
	
	$fidsssfields = $mybb->settings['customrestriction_forum'];
	$check = $mybb->settings['customrestriction_forumsetfloodctrl'];
	$floodingsystemseconds = $mybb->settings['customrestriction_forumsetfloodseconds'];
	$checksuid = $mybb->user['uid'];
	$uidsfieldsss = $mybb->settings['customrestriction_forumsetfloodusr'];



	if(($check != "0") && in_array($fid, explode(',', $fidsssfields)) && in_array($checksuid, explode(',', $uidsfieldsss)))
	{
		
		$query = $db->simple_select("posts", "*", "uid=".$mybb->user['uid']." AND fid=".$fid."", array("order_by" => 'dateline',
"order_dir" => 'DESC'));
		
		$user_lastpost = $db->fetch_array($query);
				
		$current_time = time();

		if($current_time - $user_lastpost['dateline'] <= $floodingsystemseconds) 
		{		
			$towait = ($floodingsystemseconds - ($current_time-$user_lastpost['dateline']) + 1);
			
			if($towait) {

				error("You must wait " .$towait. " seconds for posting once more");
			
			}
			return false;
		}
	}
	return true;
}


		



        /**
	    * Flood control in newreply.
	    *
	    *
	    */
function customrestriction_floodnewreply() {
	global $db, $forum, $towait, $GLOBALS, $floodingsystem, $mybb, $fid, $forums, $fids, $uid, $uids, $usergroup, $user, $datahandler, $datahandlers, $post, $posts, $time;
	
	require_once MYBB_ROOT."/inc/datahandlers/post.php";
	
	$fidsssfields = $mybb->settings['customrestriction_forum'];
	$check = $mybb->settings['customrestriction_forumsetfloodctrl'];
	$floodingsystemseconds = $mybb->settings['customrestriction_forumsetfloodseconds'];
	$checksuid = $mybb->user['uid'];
	$uidsfieldsss = $mybb->settings['customrestriction_forumsetfloodusr'];



	if(($check != "0") && in_array($fid, explode(',', $fidsssfields)) && in_array($checksuid, explode(',', $uidsfieldsss)))
	{
		
		$query = $db->simple_select("posts", "*", "uid=".$mybb->user['uid']." AND fid=".$fid."", array("order_by" => 'dateline',
"order_dir" => 'DESC'));
		
		$user_lastpost = $db->fetch_array($query);
				
		$current_time = time();

		if($current_time - $user_lastpost['dateline'] <= $floodingsystemseconds) 
		{		
			$towait = ($floodingsystemseconds - ($current_time-$user_lastpost['dateline']) + 1);
			
			if($towait) {

				error("You must wait " .$towait. " seconds for posting once more");
			
			}
			return false;
		}
	}
	return true;
}




function customrestriction_ajax_ban_userfastreply() {

	global $db, $forum, $floodingsystem, $towait, $GLOBALS, $mybb, $fid, $forums, $fids, $uid, $uids, $usergroup, $user, $datahandler, $datahandlers, $post, $posts, $time;

if($mybb->get_input('ajax', 1)) {

	if($mybb->input['ajax']) {


	require_once MYBB_ROOT."/inc/datahandlers/post.php";
	
	$fidsssfields = $mybb->settings['customrestriction_forum'];
	$check = $mybb->settings['customrestriction_forumsetfloodctrl'];
	$floodingsystemseconds = $mybb->settings['customrestriction_forumsetfloodseconds'];
	$checksuid = $mybb->user['uid'];
	$uidsfieldsss = $mybb->settings['customrestriction_forumsetfloodusr'];



	if(($check != "0") && in_array($fid, explode(',', $fidsssfields)) && in_array($checksuid, explode(',', $uidsfieldsss)))
	{
		
		$query = $db->simple_select("posts", "*", "uid=".$mybb->user['uid']." AND fid=".$fid."", array("order_by" => 'dateline',
"order_dir" => 'DESC'));
		
		$user_lastpost = $db->fetch_array($query);
				
		$current_time = time();

		if($current_time - $user_lastpost['dateline'] <= $floodingsystemseconds) 
		{		
			$towait = ($floodingsystemseconds - ($current_time-$user_lastpost['dateline']) + 1);
			
			if($towait) {

				redirect("/misc.php?action=errorpage");
			
			}
			return false;
		}
	}
	return true;




	    }
    }
}





function customrestriction_redirectpage() {

global $mybb, $templates, $noperm, $lang, $db, $header, $headerinclude, $footer;

    if($mybb->get_input('action') == 'errorpage') {

    add_breadcrumb('Error!', "misc.php?action=errorpage");

  eval('$noperm  = "' . $templates->get('customrestriction_error_template') . '";');

        output_page($noperm);
}  

	}


?>







