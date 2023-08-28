<?php

/**************************************************************************************************
*  Maximum Post per day in thread or forums to users
*
*  Plugin written and coded by The_Dark
*
*  Plugin suggested by SAEED.M
*
*  Copyright (c) 2014 The_Dark 
*
*  Version 1.0
**************************************************************************************************/


if(! defined("IN_MYBB")) { exit(); }


$plugins->add_hook("newreply_start", "maximumpostperday_newreply");;
$plugins->add_hook("datahandler_post_insert_post", "maximumpostperday_ajax");
$plugins->add_hook("newthread_start", "maximumpostperday_newthread");



function maximumpostperday_info() {

global $db, $mybb, $lang;

$lang->load("maximumpostperday");

return array(

"name"          => $lang->maximumpostperday_name,
"description"   => $lang->maximumpostperday_desc,
"website"   => "http://yugiohspirits.altervista.org/",
"author"    => "Surge&The_Dark",
"authorsite"  => "http://yugiohspirits.altervista.org/",
"version"   => "1.0",
"compatibility" => "18*"

  );

}




function maximumpostperday_activate() {

global $mybb, $db, $templates, $lang;

$lang->load("maximumpostperday");


$insertarray = array(
    "name"      => "maximumpostperday", 
    "title"     => $lang->maximumpostperday_settinggroups_title, 
    "description" => $lang->maximumpostperday_settinggroups_desc, 
    "disporder"   => 999
  );

  $gid = $db->insert_query("settinggroups", $insertarray);







  $setting= array(
    "name"      => "maximumpostperday_thread",
    "title"     => $lang->maximumpostperday_settings_thread,
    "description" => $lang->maximumpostperday_settings_thread_desc,
    "optionscode" => "text",
    "value"     => "",
    "disporder"   => 1,
    "gid"     => intval($gid)
  );


  $db->insert_query("settings", $setting);








$setting= array(
    "name"      => "maximumpostperday_howmany",
    "title"     => $lang->maximumpostperday_settings_howmany,
    "description" => $lang->maximumpostperday_settings_howmany_desc,
    "optionscode" => "numeric",
    "value"     => "",
    "disporder"   => 2,
    "gid"     => intval($gid)
  );


  $db->insert_query("settings", $setting);







$setting= array(
    "name"      => "maximumpostperday_uids",
    "title"     => $lang->maximumpostperday_settings_uids,
    "description" => $lang->maximumpostperday_settings_uids_desc,
    "optionscode" => "text",
    "value"     => "",
    "disporder"   => 3,
    "gid"     => intval($gid)
  );


  $db->insert_query("settings", $setting);






$setting= array(
    "name"      => "maximumpostperday_forum",
    "title"     => $lang->maximumpostperday_settings_forum,
    "description" => $lang->maximumpostperday_settings_forum_desc,
    "optionscode" => "text",
    "value"     => "",
    "disporder"   => 4,
    "gid"     => intval($gid)
  );


  $db->insert_query("settings", $setting);



rebuild_settings(); 

}


function maximumpostperday_deactivate() {

  global $db, $mybb;

  $db->delete_query("settinggroups", "name = 'maximumpostperday'");
  $db->delete_query("settings", "name LIKE 'maximumpostperday_%'");

  rebuild_settings();
}



  function maximumpostperday_newreply() {

    global $mybb, $thread, $tid, $threadcache, $db, $fid;

      $tids = $mybb->settings['maximumpostperday_thread'];
      $fids = $mybb->settings['maximumpostperday_forum'];
      $uids = $mybb->settings['maximumpostperday_uids'];


                  if($tids != "" || $uids != "") {

            if(in_array($tid, explode(",", $tids)) && in_array($mybb->user['uid'], explode(",", $uids)))  {

               $howmany = intval($mybb->settings['maximumpostperday_howmany']);

             $day = TIME_NOW-60*60*24;


             $query = $db->simple_select("posts", "COUNT(pid) AS post_of_today", "uid=".$mybb->user['uid']." AND visible='1' AND dateline>".$day." AND tid=".$tid."");

                $check_count = $db->fetch_field($query, "post_of_today");

             if($check_count >= $howmany) {

              error("You have finished the your max limit of posts in this thread! (".$howmany." posts)");
             }
              
          }

        }  

          if($fids != "" || $uids != "") {

          if(in_array($fid, explode(",", $fids)) && in_array($mybb->user['uid'], explode(",", $uids)))  {

             $howmany = intval($mybb->settings['maximumpostperday_howmany']);

             $day = TIME_NOW-60*60*24;


          $query = $db->simple_select("posts", "COUNT(pid) AS post_of_today", "uid=".$mybb->user['uid']." AND visible='1' AND dateline>".$day." AND fid=".$fid."");

                $check_count = $db->fetch_field($query, "post_of_today");

             if($check_count >= $howmany) {

              error("You have finished the your max limit of posts in this forum! (".$howmany." posts)");
             }


          } 

      }

    }  
  



function maximumpostperday_ajax() {

    global $mybb, $thread, $tid, $threadcache, $db, $settings, $forumcache, $forum, $fid, $forums;

      $tids = $mybb->settings['maximumpostperday_thread'];
      $fids = $mybb->settings['maximumpostperday_forum'];
      $uids = $mybb->settings['maximumpostperday_uids'];


                // load function if ajax is enabled
                
                    if($mybb->get_input('ajax', 1)) {

                if($mybb->input['ajax']) {
              
                     if($tids != "" || $uids != "") {

            if(in_array($tid, explode(",", $tids)) && in_array($mybb->user['uid'], explode(",", $uids))) {


              $howmany = intval($mybb->settings['maximumpostperday_howmany']);

             $day = TIME_NOW-60*60*24;


          $query = $db->simple_select("posts", "COUNT(pid) AS post_of_today", "uid=".$mybb->user['uid']." AND visible='1' AND dateline>".$day." AND tid=".$tid."");

                $check_count = $db->fetch_field($query, "post_of_today");

             if($check_count >= $howmany) {

              error("You have finished the your max limit of posts in this thread! (".$howmany." posts)");
          }
              
        }

      }   


                   if($fids != "" || $uids != "") {

          if(in_array($fid, explode(",", $fids)) && in_array($mybb->user['uid'], explode(",", $uids)))  {

             $howmany = intval($mybb->settings['maximumpostperday_howmany']);

             $day = TIME_NOW-60*60*24;


          $query = $db->simple_select("posts", "COUNT(pid) AS post_of_today", "uid=".$mybb->user['uid']." AND visible='1' AND dateline>".$day." AND fid=".$fid."");

                $check_count = $db->fetch_field($query, "post_of_today");

             if($check_count >= $howmany) {

              error("You have finished the your max limit of posts in this forum! (".$howmany." posts)");
             }


          } 

         }

       }
 
     }

   }      




  function maximumpostperday_newthread() {

    global $mybb, $db, $settings, $forumcache, $forum, $fid, $forums;

      $fids = $mybb->settings['maximumpostperday_forum'];
      $uids = $mybb->settings['maximumpostperday_uids'];


if($fids != "" || $uids != "") {
          
        if(in_array($fid, explode(",", $fids)) && in_array($mybb->user['uid'], explode(",", $uids))) {


              $howmany = intval($mybb->settings['maximumpostperday_howmany']);

             $day = TIME_NOW-60*60*24;


          $query = $db->simple_select("posts", "COUNT(pid) AS post_of_today", "uid=".$mybb->user['uid']." AND visible='1' AND dateline>".$day." AND fid=".$fid."");

                $check_count = $db->fetch_field($query, "post_of_today");

             if($check_count >= $howmany) {

              error("You have finished the your max limit of posts in this forum! (".$howmany." posts)");
          }
              
        }

      }

    }   




?>