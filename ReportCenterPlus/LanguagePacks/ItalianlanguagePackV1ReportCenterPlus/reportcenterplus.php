<?php

/*
*
* Questa è la versione italiana del plugin reportcenterplus (Super centro segnalazioni)
* Solo per la versione 1.0 del plugin
* Questo plugin è stato suggerito da SAEED.M, al quale pongo i miei ringraziamenti...
* Bè dopo un po' di tempo (2 mesi) finalmente questo plugin è pronto.
* Spero che vi divertirete e vi piacerà questo plugin e sceglierete ancora la nostra "ditta" Surge&The_Dark
*
*
**/

if(!defined("IN_MYBB")) { exit(); }

$plugins->add_hook("report_start", "reportcenterplus_rstart");
$plugins->add_hook("private_start", "reportcenterplus_mstart");
$plugins->add_hook("newreply_start", "reportcenterplus_nrstart");
$plugins->add_hook("newthread_start", "reportcenterplus_nstart");
$plugins->add_hook('misc_start', 'reportcenterplus_redirectpage');


function reportcenterplus_info()

{

	global $mybb, $db, $lang;

	$lang->load("reportcenterplus");

	return array(
		"name"			=> $lang->reportcenterplus_name,
		"description"	=> $lang->reportcenterplus_desc,
		"website"		=> "http://yugiohspirits.altervista.org/",
		"author"		=> "Surge&The_Dark",
		"authorsite"	=> "http://yugiohspirits.altervista.org/",
		"version"		=> "1.0",
		"compatibility"	=> "18*" 
	);
}



function reportcenterplus_activate()
{

global $db, $mybb, $templates, $lang;	

$template = '<html>
<head>
<title>{$mybb->settings[bbname]}</title>
{$headerinclude}
</head>
<body>
{$header}

<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<thead>
<tr>
<td class="thead">Errore!</td>
</tr>
</thead>
<tbody>
<tr>
<td class="trow1"><p>Ci dispiace, il seguente errore è stato riscontrato:</p>
    <p><strong>Non hai i permessi necessari per segnalare utenti!</strong></p></td>
</tr>
</tbody>
</table>
{$footer}
</body>
</html>';

$insert_array = array(
    'title' => 'reportcenterplus_error_template',
    'template' => $db->escape_string($template),
    'sid' => '-1',
    'version' => '',
    'dateline' => time()
);

$db->insert_query('templates', $insert_array);

	$lang->load('reportcenterplus');

    $insertarray = array(
		'name' => 'reportcenterplus',
		'title' => 'Report Center Plus Setting',
		'description' => "Settings for Report Center Plus",
		'disporder' => 999,
    );

	$gid = $db->insert_query("settinggroups", $insertarray);

	



	$setting = array(
		"name"			=> "reportcenterplus_nouser",
		"title"			=> $lang->reportcenterplus_settings_nouser_title,
		"description"	=> $lang->reportcenterplus_settings_nouser_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 1,
		"gid"			=> intval($gid)
	);

	$db->insert_query("settings", $setting);



$setting = array(

        "name"			=> "reportcenterplus_howmanyunread",
		"title"			=> $lang->reportcenterplus_settings_howmanyunread_title,
		"description"	=> $lang->reportcenterplus_settings_howmanyunread_desc,
		"optionscode"	=> "numeric",
		"value"			=> "0",
		"disporder"		=> 2,
		"gid"			=> intval($gid)
	);


$db->insert_query("settings", $setting);


	$setting = array(
		"name"			=> "reportcenterplus_banpmifunread",
		"title"			=> $lang->reportcenterplus_settings_banpmifunread_title,
		"description"	=> $lang->reportcenterplus_settings_banpmifunread_desc,
		"optionscode"	=> "yesno",
		"value"			=> "0",
		"disporder"		=> 3,
		"gid"			=> intval($gid)
	);

	$db->insert_query("settings", $setting);


	$setting = array(
		"name"			=> "reportcenterplus_banpostifunread",
		"title"			=> $lang->reportcenterplus_settings_banpostifunread_title,
		"description"	=> $lang->reportcenterplus_settings_banpostifunread_desc,
		"optionscode"	=> "yesno",
		"value"			=> "0",
		"disporder"		=> 4,
		"gid"			=> intval($gid)
	);

	$db->insert_query("settings", $setting);


     rebuild_settings();

}


function reportcenterplus_deactivate()

{
    global $db, $mybb, $templates;

	
    $db->delete_query("settinggroups", "name = 'reportcenterplus'");
	$db->delete_query("settings", "name LIKE 'reportcenterplus_%'");
	$db->delete_query("templates", "title = 'reportcenterplus_error_template'");

    rebuild_settings();



}



function reportcenterplus_redirectpage() {


	global $mybb, $templates, $noperms, $lang, $db, $header, $headerinclude, $footer;

    if($mybb->get_input('action') == 'redirectpage') {

    add_breadcrumb('Errore!', "misc.php?action=redirectpage");

  eval('$noperms  = "' . $templates->get('reportcenterplus_error_template') . '";');

        output_page($noperms);
}  

	}



function reportcenterplus_rstart()  {

	global $mybb, $db, $uid, $templates;

    $uid = $mybb->user['uid'];
	$uidsfields = $mybb->settings['reportcenterplus_nouser'];
	
	if($mybb->user['uid']) {

if(in_array($uid, explode(',', $uidsfields)))

 {
redirect("/misc.php?action=redirectpage");
 } 

}



}


function reportcenterplus_mstart() {

	global $db, $forum, $floodingsystem, $mybb, $fid, $forums, $fids, $uid, $uids, $usergroup, $user, $datahandler, $datahandlers, $post, $posts, $time;
		

	$settingbanpm = $mybb->settings['reportcenterplus_banpmifunread'];

	if($settingbanpm != "0") {
		$unreadcount = 0;
		$reportedcontent = array(); 
	    $query=$db->simple_select("reportedcontent", "*", "reportstatus='0'");
	    while ($reportedcontenttemp = $db->fetch_array($query)) {
	    	array_push($reportedcontent, $reportedcontenttemp);
	    	++$unreadcount;
	    }

	    $defaultnumber = is_numeric($mybb->settings['reportcenterplus_howmanyunread']);
	    if($unreadcount >= $defaultnumber) {

			$query=$db->simple_select("posts", "*", "uid=".$mybb->user['uid']);
			while ($currentpost = $db->fetch_array($query)) {
	    		foreach ($reportedcontent as $value) {
	    				if($currentpost['pid'] == $value['id']) {
	    					error("Non puoi visualizzare questa pagina perchè sei stato segnalato da un altro utente, aspetta che il Moderatore legga la segnalazione a tuo carico, e poi ricarica la pagina!");
	    			}
	    		}	
	    	}
		}
	}
}






function reportcenterplus_nstart() {

	global $db, $forum, $floodingsystem, $mybb, $fid, $forums, $fids, $uid, $uids, $usergroup, $user, $datahandler, $datahandlers, $post, $posts, $time;
		

	$settingbanpost = $mybb->settings['reportcenterplus_banpostifunread'];

	if($settingbanpost != "0") {
		$unreadcount = 0;
		$reportedcontent = array(); 
	    $query=$db->simple_select("reportedcontent", "*", "reportstatus='0'");
	    while ($reportedcontenttemp = $db->fetch_array($query)) {
	    	array_push($reportedcontent, $reportedcontenttemp);
	    	++$unreadcount;
	    }

	    $defaultnumber = is_numeric($mybb->settings['reportcenterplus_howmanyunread']);
	    if($unreadcount >= $defaultnumber) {

			$query=$db->simple_select("posts", "*", "uid=".$mybb->user['uid']);
			while ($currentpost = $db->fetch_array($query)) {
	    		foreach ($reportedcontent as $value) {
	    				if($currentpost['pid'] == $value['id']) {

	    					error("Non puoi visualizzare questa pagina perchè sei stato segnalato da un altro utente, aspetta che il Moderatore legga la segnalazione a tuo carico, e poi ricarica la pagina!");
	    			}
	    		}	
	    	}
		}
	}
}





function reportcenterplus_nrstart() {

	global $db, $forum, $floodingsystem, $mybb, $fid, $forums, $fids, $uid, $uids, $usergroup, $user, $datahandler, $datahandlers, $post, $posts, $time;
		

	$settingbanpost = $mybb->settings['reportcenterplus_banpostifunread'];

	if($settingbanpost != "0") {
		$unreadcount = 0;
		$reportedcontent = array(); 
	    $query=$db->simple_select("reportedcontent", "*", "reportstatus='0'");
	    while ($reportedcontenttemp = $db->fetch_array($query)) {
	    	array_push($reportedcontent, $reportedcontenttemp);
	    	++$unreadcount;
	    }

	    $defaultnumber = is_numeric($mybb->settings['reportcenterplus_howmanyunread']);
	    if($unreadcount >= $defaultnumber) {

			$query=$db->simple_select("posts", "*", "uid=".$mybb->user['uid']);
			while ($currentpost = $db->fetch_array($query)) {
	    		foreach ($reportedcontent as $value) {
	    				if($currentpost['pid'] == $value['id']) {
	    					
	    				 error("Non puoi visualizzare questa pagina perchè sei stato segnalato da un altro utente, aspetta che il Moderatore legga la segnalazione a tuo carico, e poi ricarica la pagina!");
	    			}
	    		}	
	    	}
		}
	}
}
