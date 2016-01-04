<?php
#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include student demographic info, scheduling, grade book, attendance, 
#  report cards, eligibility, transcripts, parent portal, 
#  student portal and more.   
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send 
#  an email to info@os4ed.com.
#
#  This program is released under the terms of the GNU General Public License as  
#  published by the Free Software Foundation, version 2 of the License. 
#  See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#***************************************************************************************
include('../../RedirectModulesInc.php');

while(!UserSyear())
{
    session_write_close();
    session_start();
}

$current_hour = date('H');
$welcome .= 'User:'. User('NAME');
if($_SESSION['LAST_LOGIN'])

$welcome .= ' | Last login: '.ProperDate(substr($_SESSION['LAST_LOGIN'],0,10)).' at ' .substr($_SESSION['LAST_LOGIN'],10);

if($_SESSION['FAILED_LOGIN'])
$welcome .= ' | <span class=red >'.$_SESSION['FAILED_LOGIN'].'</b> failed login attempts</span>';

//----------------------------------------Update Missing Attendance_________________________________-

echo '<div id="calculating" style="display: none; padding-top:20px; padding-bottom:15px;"><img src="assets/missing_attn_loader.gif" /><br/><br/><br/><span style="color:#c90000;"><span style=" font-size:15px; font-weight:bold;">Please wait.</span><br /><span style=" font-size:12px;">Compiling missing attendance data. Do not click anywhere.</span></span></div>
<div id="resp" style="font-size:14px"></div>';

//-----------------------------------------Update missing attendance ends--------------------------------------------------

$userName=  User('USERNAME');
 $link=array();
    $id_arr=array();
    $arr=array();

    $qr="select to_user,mail_id,to_cc,to_bcc from msg_inbox where isdraft is NULL";
    $fetch=DBGet(DBQuery($qr));

    foreach($fetch as $key =>$value)
    {
         $to=$value['TO_USER'];"<br>";
         $cc=$value['TO_CC'];
         $bcc=$value['TO_BCC'];
$mul=$value['TO_MULTIPLE_USERS'];
$mul_cc=$value['TO_CC_MULTIPLE'];
$mul_bcc=$value['TO_BCC_MULTIPLE'];
        $to_arr=explode(',',$to);
         $arr_cc=explode(',',$cc);
         $arr_bcc=explode(',',$bcc);
         $arr_mul=explode(',',$mul);
          
        if(in_array($userName,$to_arr) || in_array($userName,$arr_mul)  || in_array($userName,$arr_bcc) ||  in_array($userName,$arr_cc) || in_array($userName,$arr_cc) || in_array($userName,$arr_bcc))
        {
           array_push($id_arr,$value['MAIL_ID']);          
        }
       
    }
   
   
      $total_count=count($id_arr);
    if($total_count>0)
     $to_user_id=implode(',',$id_arr);
    else
        $to_user_id='null';
  $inbox="select count(*) as total from msg_inbox where mail_id in($to_user_id) and FIND_IN_SET('$userName', mail_read_unread )";
$in=DBGet(DBQuery($inbox));
 $in=$in[1]['TOTAL'];
//print_r($inbox_info);
$inbox_info=$total_count-$in;
if($inbox_info>1)
{
    echo "<p><font color=#FF0000><b>You have ".$inbox_info." unread messages</b></font></p>";
}
 else 
{
    if($inbox_info==1)
        echo "<p><font color=#FF0000><b>You have 1 unread message</b></font></p>";

}
if($_SESSION['PROFILE_ID']==0)
    $title1='Super Administrator';
if($_SESSION['PROFILE_ID']==1)
    $title1='Administrator';
 User('PROFILE');
switch (User('PROFILE'))
{
    
    case 'admin':
        DrawBC ($welcome. ' | Role: '.$title1);
        
                    $reassign_cp=  DBGet(DBQuery('SELECT COURSE_PERIOD_ID ,TEACHER_ID,PRE_TEACHER_ID,ASSIGN_DATE,COURSE_PERIOD_ID FROM teacher_reassignment WHERE ASSIGN_DATE <= \''.date('Y-m-d').'\' AND UPDATED=\'N\' '));
                    foreach($reassign_cp as $re_key=>$reassign_cp_value)
                    {
                        
                        if(strtotime($reassign_cp_value['ASSIGN_DATE'])<=  strtotime(date('Y-m-d')))
                        {                           
                        $get_pname=DBGet(DBQuery("SELECT CONCAT(sp.title,IF(cp.mp!='FY',CONCAT(' - ',mp.short_name),' '),IF(CHAR_LENGTH(cpv.days)<5,CONCAT(' - ',cpv.days),' '),' - ',cp.short_name,' - ',CONCAT_WS(' ',st.first_name,st.middle_name,st.last_name)) AS CP_NAME FROM course_periods cp,course_period_var cpv,school_periods sp,marking_periods mp,staff st WHERE cpv.period_id=sp.period_id and cp.marking_period_id=mp.marking_period_id and st.staff_id=".$reassign_cp_value['TEACHER_ID']."  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=".$reassign_cp_value['COURSE_PERIOD_ID']));
                        $get_pname=$get_pname[1]['CP_NAME'];
                        DBQuery('UPDATE course_periods SET title=\''.$get_pname.'\', teacher_id='.$reassign_cp_value['TEACHER_ID'].' WHERE COURSE_PERIOD_ID='.$reassign_cp_value['COURSE_PERIOD_ID']); 
                        DBQuery('UPDATE teacher_reassignment SET updated=\'Y\' WHERE assign_date <=CURDATE() AND updated=\'N\' AND COURSE_PERIOD_ID='.$reassign_cp_value['COURSE_PERIOD_ID']);
                        DBQuery('UPDATE missing_attendance SET TEACHER_ID='.$reassign_cp_value['TEACHER_ID'].' WHERE TEACHER_ID='.$reassign_cp_value['PRE_TEACHER_ID'].' AND COURSE_PERIOD_ID='.$reassign_cp_value['COURSE_PERIOD_ID']); 
                        }
                    }

            $schedule_exit=DBGet(DBQuery('SELECT ID FROM schedule WHERE syear=\''.  UserSyear().'\' AND school_id=\''.UserSchool().'\'  LIMIT 0,1'));
            
            if($schedule_exit[1]['ID']!='')
            {
                    $last_update=DBGet(DBQuery('SELECT VALUE FROM program_config WHERE PROGRAM=\'MissingAttendance\' AND TITLE=\'LAST_UPDATE\' AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
                    if($last_update[1]['VALUE']!='')
                    {
                        if($last_update[1]['VALUE'] < date('Y-m-d'))
                        {
                            echo '<script type=text/javascript>calculate_missing_atten();</script>';
                        }
                    }
            }
          
//        $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.last_updated,CONCAT(\'<b>\',pn.TITLE,\'</b>\') AS TITLE,pn.CONTENT 
//                                    FROM portal_notes pn
//                                    WHERE pn.SYEAR=\''.UserSyear().'\' AND pn.START_DATE<=CURRENT_DATE AND 
//                                        (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
//                                        AND (pn.school_id IS NULL OR pn.school_id IN('.  GetUserSchools(UserID(), true).'))
//                                        AND ('.(User('PROFILE_ID')==''?' FIND_IN_SET(\'admin\', pn.PUBLISHED_PROFILES)>0':' FIND_IN_SET('.User('PROFILE_ID').',pn.PUBLISHED_PROFILES)>0)').
//                                        'ORDER BY pn.SORT_ORDER,pn.last_updated DESC'),array('last_updated'=>'ProperDate','CONTENT'=>'_nl2br'));
          
//                      echo 'SELECT IF(pn.published_profiles like\'%all%\',\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.last_updated,CONCAT(\'<b>\',pn.TITLE,\'</b>\') AS TITLE,pn.CONTENT 
//                                    FROM portal_notes pn
//                                    WHERE pn.SYEAR=\''.UserSyear().'\' AND pn.START_DATE<=CURRENT_DATE AND 
//                                        (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
//                                        AND (pn.published_profiles like\'%all%\' OR pn.school_id IN('.  UserSchool().'))
//                                        AND ('.(User('PROFILE_ID')==''?' FIND_IN_SET(\'admin\', pn.PUBLISHED_PROFILES)>0':' FIND_IN_SET('.User('PROFILE_ID').',pn.PUBLISHED_PROFILES)>0)').
//                                        'ORDER BY pn.SORT_ORDER,pn.last_updated DESC';               
            $notes_RET = DBGet(DBQuery('SELECT IF(pn.published_profiles like\'%all%\',\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,CONCAT(\'<b>\',pn.TITLE,\'</b>\') AS TITLE,pn.CONTENT 
                                    FROM portal_notes pn
                                    WHERE pn.SYEAR=\''.UserSyear().'\' AND pn.START_DATE<=CURRENT_DATE AND 
                                        (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
                                        AND (pn.published_profiles like\'%all%\' OR pn.school_id IN('.  UserSchool().'))
                                        AND ('.(User('PROFILE_ID')==''?' FIND_IN_SET(\'admin\', pn.PUBLISHED_PROFILES)>0':' FIND_IN_SET('.User('PROFILE_ID').',pn.PUBLISHED_PROFILES)>0)').
                                        'ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'),array('LAST_UPDATED'=>'ProperDate','CONTENT'=>'_nl2br'));
        if(count($notes_RET))
        {

            echo '<div>';
            ListOutput($notes_RET,array('LAST_UPDATED'=>'Date Posted','TITLE'=>'Title','CONTENT'=>'Note','SCHOOL'=>'School'),'Note','Notes',array(),array(),array('save'=>false,'search'=>false));
            echo '</div>';
        }

          $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,calendar_events_visibility cev,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\''.UserSyear().'\'
                    AND ce.SCHOOL_ID IN('.  GetUserSchools(UserID(), true).')
                    AND s.ID=ce.SCHOOL_ID AND (ce.CALENDAR_ID=cev.CALENDAR_ID)
                    AND '.(User('PROFILE_ID')==''?'cev.PROFILE=\'admin\'':'cev.PROFILE_ID=\''.User('PROFILE_ID')).'\' 
                    ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));

          $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\''.UserSyear().'\'
                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
            $event_count=count($events_RET)+1;
          foreach ($events_RET1 as $events_RET_key => $events_RET_value) 
          {
              $events_RET[$event_count]=$events_RET_value;
              $event_count++;
          }
        

        if(count($events_RET))
        {
            echo '<p>';
            ListOutput($events_RET,array('SCHOOL_DATE'=>'Date','TITLE'=>'Event','DESCRIPTION'=>'Description','SCHOOL'=>'School'),'Upcoming Event','Upcoming Events',array(),array(),array('save'=>false,'search'=>false));
            echo '</p>';
        }

        # ------------------------------------ Original Raw Query Start ------------------------------------------------ #

               if(Preferences('HIDE_ALERTS')!='Y')
        {
	$RET=DBGet(DBQuery('SELECT SCHOOL_ID,SCHOOL_DATE,COURSE_PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID FROM missing_attendance WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.  UserSyear().'\' AND SCHOOL_DATE<\''.date('Y-m-d').'\' LIMIT 0,1 '));
          if (count($RET))
          {
                echo '<p><font color=#FF0000><b>Warning!! - Teachers have missing attendance. Go to : Users -> Teacher Programs -> Missing Attendance</b></font></p>';
          }
        }
        echo '<div id="attn_alert" style="display: none" ><p><font color=#FF0000><b>Warning!! - Teachers have missing attendance. Go to : Users -> Teacher Programs -> Missing Attendance</b></font></p></div>';
        //-------------------------------------------------------------------------------ROLLOVER NOTIFICATION STARTS----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
        
       $notice_date=DBGet(DBQuery('SELECT END_DATE FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
        $notice_roll_date=DBGet(DBQuery('SELECT SYEAR FROM school_years WHERE SYEAR>\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
        $rolled=count($notice_roll_date);
        $last_date=strtotime($notice_date[1]['END_DATE'])-strtotime(DBDate());
        $last_date=$last_date/(60*60*24);
        if($last_date<=15 && $rolled==0 )
        {
            echo '<p><font color=#FF0000><b>School year is ending or has ended. Rollover required.</b></font></p>';
        }
        //-------------------------------------------------------------------------------ROLLOVER NOTIFICATION ENDS----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

       
        break;

    case 'teacher':
        DrawBC ($welcome.' | Role: Teacher');
        $att_qry=DBGet(DBQuery('SELECT Count(1) as count FROM  profile_exceptions WHERE MODNAME 
                  IN (\'attendance/TakeAttendance.php\',\'attendance/DailySummary.php\',\'attendance/StudentSummary\') AND 
                  PROFILE_ID='.User('PROFILE_ID').' AND CAN_USE=\'Y\' '));
        
                    $reassign_cp=  DBGet(DBQuery('SELECT COURSE_PERIOD_ID ,TEACHER_ID,PRE_TEACHER_ID,ASSIGN_DATE FROM teacher_reassignment WHERE ASSIGN_DATE <= \''.date('Y-m-d').'\' AND UPDATED=\'N\' '));
                    foreach($reassign_cp as $re_key=>$reassign_cp_value)
                    {
                        if(strtotime($reassign_cp_value['ASSIGN_DATE'])<= strtotime(date('Y-m-d')))
                        {   
                        $get_pname=DBGet(DBQuery("SELECT CONCAT(sp.title,IF(cp.mp!='FY',CONCAT(' - ',mp.short_name),' '),IF(CHAR_LENGTH(cpv.days)<5,CONCAT(' - ',cpv.days),' '),' - ',cp.short_name,' - ',CONCAT_WS(' ',st.first_name,st.middle_name,st.last_name)) AS CP_NAME FROM course_periods cp,course_period_var cpv,school_periods sp,marking_periods mp,staff st WHERE cpv.period_id=sp.period_id and cp.marking_period_id=mp.marking_period_id and st.staff_id=".$reassign_cp_value['TEACHER_ID']."  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=".$reassign_cp_value['COURSE_PERIOD_ID']));
                        $get_pname=$get_pname[1]['CP_NAME'];
                        DBQuery('UPDATE course_periods SET title=\''.$get_pname.'\', teacher_id='.$reassign_cp_value['TEACHER_ID'].' WHERE COURSE_PERIOD_ID='.$reassign_cp_value['COURSE_PERIOD_ID']); 
                        DBQuery('UPDATE teacher_reassignment SET updated=\'Y\' WHERE assign_date <=CURDATE() AND updated=\'N\' AND COURSE_PERIOD_ID='.$reassign_cp_value['COURSE_PERIOD_ID']);
                        DBQuery('UPDATE missing_attendance SET TEACHER_ID='.$reassign_cp_value['TEACHER_ID'].' WHERE TEACHER_ID='.$reassign_cp_value['PRE_TEACHER_ID'].' AND COURSE_PERIOD_ID='.$reassign_cp_value['COURSE_PERIOD_ID']); 
                        }
                        
                    }
            $schedule_exit=DBGet(DBQuery('SELECT ID FROM schedule WHERE syear=\''.  UserSyear().'\' AND school_id=\''.UserSchool().'\' LIMIT 0,1'));
            if($schedule_exit[1]['ID']!='')
            {
                    $last_update=DBGet(DBQuery('SELECT VALUE FROM program_config WHERE PROGRAM=\'MissingAttendance\' AND TITLE=\'LAST_UPDATE\' AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
                    if($last_update[1]['VALUE']!='')
                    {
                        if($last_update[1]['VALUE'] < date('Y-m-d'))
                        {
                            echo '<script type=text/javascript>calculate_missing_atten();</script>';
                        }
                    }
            }
            $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,CONCAT(\'<b>\',pn.TITLE,\'</b>\') AS TITLE,pn.CONTENT 
                            FROM portal_notes pn
                            WHERE pn.SYEAR=\''.UserSyear().'\' AND pn.START_DATE<=CURRENT_DATE AND 
                                (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
                                AND (pn.school_id IS NULL OR pn.school_id IN('.  GetUserSchools(UserID(), true).'))
                                AND ('.(User('PROFILE_ID')==''?' FIND_IN_SET(\'teacher\', pn.PUBLISHED_PROFILES)>0':' FIND_IN_SET('.User('PROFILE_ID').',pn.PUBLISHED_PROFILES)>0)').'
                                ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'),array('LAST_UPDATED'=>'ProperDate','CONTENT'=>'_nl2br'));

        if(count($notes_RET))
        {
            echo '<p>';
            ListOutput($notes_RET,array('LAST_UPDATED'=>'Date Posted','TITLE'=>'Title','CONTENT'=>'Note','SCHOOL'=>'School'),'Note','Notes',array(),array(),array('save'=>false,'search'=>false));
            echo '</p>';
        }

        
        $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,calendar_events_visibility cev,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\''.UserSyear().'\'
                    AND ce.school_id IN('.  GetUserSchools(UserID(), true).')
                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID 
                    AND '.(User('PROFILE_ID')==''?'cev.PROFILE=\'teacher\'':'cev.PROFILE_ID='.User('PROFILE_ID')).' 
                    ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
        $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\''.UserSyear().'\'
                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
            $event_count=count($events_RET)+1;
          foreach ($events_RET1 as $events_RET_key => $events_RET_value) 
          {
              $events_RET[$event_count]=$events_RET_value;
              $event_count++;
          }
        if(count($events_RET))
        {
            echo '<p>';
            ListOutput($events_RET,array('SCHOOL_DATE'=>'Date','TITLE'=>'Event','DESCRIPTION'=>'Description','SCHOOL'=>'School'),'Upcoming Event','Upcoming Events',array(),array(),array('save'=>false,'search'=>false));
            echo '</p>';
        }
        if($att_qry[1]['count']!=0)
        echo '<div id="attn_alert" style="display: none" ><p><font color=#FF0000><b>Warning!! - Teachers have missing attendance. Go to : Users -> Teacher Programs -> Missing Attendance</b></font></p></div>';
        if(Preferences('HIDE_ALERTS')!='Y')
        {
            // warn if missing attendance
            
			if($_REQUEST['modfunc']=='attn')
                        {
                            header("Location:Modules.php?modname=users/TeacherPrograms.php?include=attendance/TakeAttendance.php");
                        }



$RET=DBGet(DBQuery('SELECT DISTINCT s.TITLE AS SCHOOL,mi.SCHOOL_DATE,cp.TITLE AS TITLE,mi.COURSE_PERIOD_ID,mi.PERIOD_ID,cpv.ID AS CPV_ID 
    FROM missing_attendance mi,schools s,course_periods cp,course_period_var cpv WHERE s.ID=mi.SCHOOL_ID AND  cp.COURSE_PERIOD_ID=mi.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND (mi.TEACHER_ID=\''.User('STAFF_ID').'\' OR mi.SECONDARY_TEACHER_ID=\''.  User('STAFF_ID').'\' ) AND mi.SCHOOL_ID=\''.UserSchool().'\' AND mi.SYEAR=\''.UserSyear().'\' AND mi.SCHOOL_DATE < \''.DBDate().'\' AND (mi.SCHOOL_DATE=cpv.COURSE_PERIOD_DATE OR POSITION(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Thu\',\'H\',(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Sun\',\'U\',SUBSTR(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\'),1,1)))) IN cpv.DAYS)>0) ORDER BY cp.TITLE,mi.SCHOOL_DATE '),array('SCHOOL_DATE'=>'ProperDate'));
$codes_RET_count = DBGet(DBQuery('SELECT COUNT(*) AS CODES FROM attendance_codes WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\'  AND TYPE=\'teacher\' AND TABLE_NAME=\'0\' ORDER BY SORT_ORDER'));

if (count($RET) && $codes_RET_count[1]['CODES'])
{
    echo '<p><center><font color=#FF0000><b>Warning!!</b></font> - Teachers have missing attendance data:</center>';

    $modname = 'users/TeacherPrograms.php?include=attendance/TakeAttendance.php';
    $link['remove']['link'] = "Modules.php?modname=$modname&modfunc=attn&attn=miss";
    $link['remove']['variables'] = array('date'=>'SCHOOL_DATE','cp_id'=>'COURSE_PERIOD_ID','cpv_id'=>'CPV_ID');
    $_SESSION['take_mssn_attn']=true;
   ListOutput_missing_attn_teach_port($RET,array('SCHOOL_DATE'=>'Date','TITLE'=>'Period -Teacher','SCHOOL'=>'School'),'Period','Periods',$link,array(),array('save'=>false,'search'=>false));
   

    echo '</p>';
}
        }

   

	break;

    case 'parent':
        DrawBC ($welcome.' | Role: Parent');
        $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,pn.TITLE,pn.CONTENT 
            FROM portal_notes pn
            WHERE pn.SYEAR=\''.UserSyear().'\' 
                AND pn.START_DATE<=CURRENT_DATE AND (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL) 
                AND (pn.school_id IS NULL OR pn.school_id IN('.  GetUserSchools(UserID(), true).'))
                AND ('.(User('PROFILE_ID')==''?' FIND_IN_SET(\'parent\', pn.PUBLISHED_PROFILES)>0':' FIND_IN_SET('.User('PROFILE_ID').',pn.PUBLISHED_PROFILES)>0)').'
                ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'),array('LAST_UPDATED'=>'ProperDate','CONTENT'=>'_nl2br'));

        if(count($notes_RET))
        {
            echo '<p>';
            ListOutput($notes_RET,array('LAST_UPDATED'=>'Date Posted','TITLE'=>'Title','CONTENT'=>'Note','SCHOOL'=>'School'),'Note','Notes',array(),array(),array('save'=>false,'search'=>false));
            echo '</p>';
        }

        $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,calendar_events_visibility cev,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\''.UserSyear().'\'
                    AND ce.school_id IN('.  GetUserSchools(UserID(), true).')
                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID 
                    AND '.(User('PROFILE_ID')==''?'cev.PROFILE=\'parent\'':'cev.PROFILE_ID='.User('PROFILE_ID')).' 
                    ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
        $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\''.UserSyear().'\'
                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
            $event_count=count($events_RET)+1;
          foreach ($events_RET1 as $events_RET_key => $events_RET_value) 
          {
              $events_RET[$event_count]=$events_RET_value;
              $event_count++;
          }
        if(count($events_RET))
        {
            echo '<p>';
            ListOutput($events_RET,array('SCHOOL_DATE'=>'Date','TITLE'=>'Event','DESCRIPTION'=>'Description','SCHOOL'=>'School'),'Upcoming Event','Upcoming Events',array(),array(),array('save'=>false,'search'=>false));
            echo '</p>';
        }

    
	  

$courses_RET=  DBGet(DBQuery('SELECT DISTINCT c.TITLE ,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TEACHER_ID AS STAFF_ID FROM schedule s,course_periods cp,course_period_var cpv,courses c,attendance_calendar acc WHERE s.SYEAR=\''.UserSyear().'\' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND s.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE ) AND (\''.DBDate().'\' BETWEEN s.START_DATE AND s.END_DATE OR \''.DBDate().'\'>=s.START_DATE AND s.END_DATE IS NULL) AND s.STUDENT_ID=\''.UserStudentID().'\' AND cp.GRADE_SCALE_ID IS NOT NULL'.(User('PROFILE')=='teacher'?' AND cp.TEACHER_ID=\''.User('STAFF_ID').'\'':'').' AND c.COURSE_ID=cp.COURSE_ID ORDER BY (SELECT SORT_ORDER FROM school_periods WHERE PERIOD_ID=cpv.PERIOD_ID)'));


foreach($courses_RET as $course)
	{
            $staff_id = $course['STAFF_ID'];
            $assignments_Graded = DBGet(DBQuery( 'SELECT gg.STUDENT_ID,ga.ASSIGNMENT_ID,gg.POINTS,gg.COMMENT,ga.TITLE,ga.DESCRIPTION,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                   FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg
                                                  ON (gg.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.STUDENT_ID=\''.UserStudentID().'\'),gradebook_assignment_types at
                                                  WHERE (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID=\''.$course[COURSE_ID].'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID=\''.UserMP().'\'
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND (gg.POINTS IS NOT NULL) AND (ga.POINTS!=\'0\' OR gg.POINTS IS NOT NULL AND gg.POINTS!=\'-1\') ORDER BY ga.ASSIGNMENT_ID DESC'));
          
            foreach($assignments_Graded AS $assignments_Graded)
            $GRADED_ASSIGNMENT_ID[]= $assignments_Graded['ASSIGNMENT_ID'];
            $ASSIGNMENT_ID_GRADED = implode(",", $GRADED_ASSIGNMENT_ID);
           
           $GRADED_ASSIGNMENT = '( '.$ASSIGNMENT_ID_GRADED.' )';
		   
          if(count($assignments_Graded))
		  {
         $assignments_RET = DBGet(DBQuery( 'SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.DESCRIPTION as COMMENT,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                   FROM gradebook_assignments ga
                                                 ,gradebook_assignment_types at
                                                  WHERE ga.ASSIGNMENT_ID NOT IN '.$GRADED_ASSIGNMENT.' AND (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID='.$course[COURSE_ID].' AND ga.STAFF_ID='.$staff_id.') AND ga.MARKING_PERIOD_ID=\''.UserMP().'\'
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND(  CURRENT_DATE>=ga.ASSIGNED_DATE OR CURRENT_DATE<=ga.ASSIGNED_DATE )AND ga.DUE_DATE IS NOT NULL AND CURRENT_DATE<=ga.DUE_DATE
                                                   AND (ga.POINTS!=\'0\') ORDER BY ga.ASSIGNMENT_ID DESC'));
		   }
         else
		 {
          $assignments_RET = DBGet(DBQuery( 'SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.DESCRIPTION as COMMENT,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                   FROM gradebook_assignments ga
                                                 ,gradebook_assignment_types at
                                                  WHERE (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID=\''.$course[COURSE_ID].'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID=\''.UserMP().'\'
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND( CURRENT_DATE>=ga.ASSIGNED_DATE OR CURRENT_DATE<=ga.ASSIGNED_DATE)AND ga.DUE_DATE IS NOT NULL AND CURRENT_DATE<=ga.DUE_DATE
                                                   AND (ga.POINTS!=\'0\') ORDER BY ga.ASSIGNMENT_ID DESC'));
												   
			}
     
	
               if(count($assignments_RET))
		{
			
			
			$LO_columns = array('TITLE'=>'Title','CATEGORY'=>'Category','ASSIGNED_DATE'=>'Assigned Date','DUE_DATE'=>'Due Date','COMMENT'=>'Description');

			$LO_ret = array(0=>array());
                        foreach($assignments_RET as $assignment)
			{
                        $LO_ret[] = array('TITLE'=>$assignment['TITLE'],'CATEGORY'=>$assignment['CATEGORY'],'ASSIGNED_DATE'=>$assignment['ASSIGNED_DATE'],'DUE_DATE'=>$assignment['DUE_DATE'],'COMMENT'=>html_entity_decode($assignment['COMMENT']));
			}
                        DrawHeader('Subject - '.substr($course['TITLE'],strrpos(str_replace(' - ',' ^ ',$course['TITLE']),'^')));
			
			unset($LO_ret[0]);
			ListOutput($LO_ret,$LO_columns,'Assignment','Assignments',array(),array(),array('center'=>false,'save'=>$_REQUEST['id']!='all','search'=>false));
		}
        }

 break;

    case 'student':
        DrawBC ($welcome.' | Role: Student');

        $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,pn.TITLE,pn.CONTENT 
            FROM portal_notes pn
            WHERE pn.SYEAR=\''.UserSyear().'\' 
                AND pn.START_DATE<=CURRENT_DATE AND (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL) 
                AND (pn.school_id IS NULL OR pn.SCHOOL_ID=\''.UserSchool().'\') 
                AND  position(\',0,\' IN pn.PUBLISHED_PROFILES)>0
                ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'),array('LAST_UPDATED'=>'ProperDate','CONTENT'=>'_nl2br'));

        if(count($notes_RET))
        {
            echo '<p>';
            ListOutput($notes_RET,array('LAST_UPDATED'=>'Date Posted','TITLE'=>'Title','CONTENT'=>'Note'),'Note','Notes',array(),array(),array('save'=>false,'search'=>false));
            echo '</p>';
        }

        
          $events_RET = DBGet(DBQuery("SELECT TITLE,SCHOOL_DATE,DESCRIPTION FROM calendar_events ce,calendar_events_visibility cev WHERE ce.calendar_id=cev.calendar_id AND cev.profile_id=3 AND SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE+30 AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
          $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\''.UserSyear().'\'
                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
            $event_count=count($events_RET)+1;
          foreach ($events_RET1 as $events_RET_key => $events_RET_value) 
          {
              $events_RET[$event_count]=$events_RET_value;
              $event_count++;
          }
        if(count($events_RET))
        {
            echo '<p>';
            ListOutput($events_RET,array('TITLE'=>'Event','SCHOOL_DATE'=>'Date','DESCRIPTION'=>'Description'),'Upcoming Event','Upcoming Events',array(),array(),array('save'=>false,'search'=>false));
            echo '</p>';
        }
		
		
$sql = 'SELECT s.STAFF_ID,CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME) AS FULL_NAME,sp.TITLE,cp.PERIOD_ID
		FROM staff s,course_periods cp,school_periods sp, attendance_calendar acc
		WHERE
			sp.PERIOD_ID = cp.PERIOD_ID AND cp.GRADE_SCALE_ID IS NOT NULL
			AND cp.TEACHER_ID=s.STAFF_ID AND cp.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE )
			AND cp.SYEAR=\''.UserSyear().'\' AND cp.SCHOOL_ID=\''.UserSchool().'\' AND s.PROFILE=\'teacher\'
			'.(($_REQUEST['period'])?' AND cp.PERIOD_ID=\''.$_REQUEST[period].'\'':'').'
			AND NOT EXISTS (SELECT \'\' FROM grades_completed ac WHERE ac.STAFF_ID=cp.TEACHER_ID AND ac.MARKING_PERIOD_ID=\''.$_REQUEST[mp].'\' AND ac.PERIOD_ID=sp.PERIOD_ID)
		';
	  


$courses_RET=  DBGet(DBQuery('SELECT DISTINCT c.TITLE ,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TEACHER_ID AS STAFF_ID FROM schedule s,course_periods cp,courses c,attendance_calendar acc WHERE s.SYEAR=\''.UserSyear().'\' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND s.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE ) AND (\''.DBDate().'\' BETWEEN s.START_DATE AND s.END_DATE OR \''.DBDate().'\'>=s.START_DATE AND s.END_DATE IS NULL) AND s.STUDENT_ID=\''.UserStudentID().'\' AND cp.GRADE_SCALE_ID IS NOT NULL'.(User('PROFILE')=='teacher'?' AND cp.TEACHER_ID=\''.User('STAFF_ID').'\'':'').' AND c.COURSE_ID=cp.COURSE_ID ORDER BY (SELECT SORT_ORDER FROM school_periods WHERE PERIOD_ID=cp.course_period_id)'));




foreach($courses_RET as $course)
	{
            $staff_id = $course['STAFF_ID'];
            $assignments_Graded = DBGet(DBQuery( 'SELECT gg.STUDENT_ID,ga.ASSIGNMENT_ID,gg.POINTS,gg.COMMENT,ga.TITLE,ga.DESCRIPTION,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                   FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg
                                                  ON (gg.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.STUDENT_ID=\''.UserStudentID().'\'),gradebook_assignment_types at
                                                  WHERE (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID=\''.$course[COURSE_ID].'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID=\''.UserMP().'\'
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND (gg.POINTS IS NOT NULL) AND (ga.POINTS!=\'0\' OR gg.POINTS IS NOT NULL AND gg.POINTS!=\'-1\') ORDER BY ga.ASSIGNMENT_ID DESC'));

            foreach($assignments_Graded AS $assignments_Graded)
            $GRADED_ASSIGNMENT_ID[]= $assignments_Graded['ASSIGNMENT_ID'];
            $ASSIGNMENT_ID_GRADED = implode(",", $GRADED_ASSIGNMENT_ID);

           $GRADED_ASSIGNMENT = '( '.$ASSIGNMENT_ID_GRADED.' )';
		   
		   
		   
		   
        if(count($assignments_Graded))
		{
         $assignments_RET = DBGet(DBQuery( 'SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.DESCRIPTION as COMMENT,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY FROM gradebook_assignments ga, gradebook_assignment_types at                   WHERE ga.ASSIGNMENT_ID NOT IN '.$GRADED_ASSIGNMENT.' AND (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID=\''.$course[COURSE_ID].'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID=\''.UserMP().'\'
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND( CURRENT_DATE>=ga.ASSIGNED_DATE OR CURRENT_DATE<=ga.ASSIGNED_DATE)AND ga.DUE_DATE IS NOT NULL AND CURRENT_DATE<=ga.DUE_DATE
                                                   AND (ga.POINTS!=\'0\') ORDER BY ga.ASSIGNMENT_ID DESC'));
		}
        else
		 {
          $assignments_RET = DBGet(DBQuery( 'SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.DESCRIPTION as COMMENT,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                   FROM gradebook_assignments ga
                                                 ,gradebook_assignment_types at
                                                  WHERE (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID=\''.$course[COURSE_ID].'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID=\''.UserMP().'\'
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND( CURRENT_DATE>=ga.ASSIGNED_DATE OR CURRENT_DATE<=ga.ASSIGNED_DATE)AND ga.DUE_DATE IS NOT NULL AND CURRENT_DATE<=ga.DUE_DATE
                                                   AND (ga.POINTS!=\'0\') ORDER BY ga.ASSIGNMENT_ID DESC'));
		}


			if(count($assignments_RET))
			{
	
				
				$LO_columns = array('TITLE'=>'Title','CATEGORY'=>'Category','ASSIGNED_DATE'=>'Assigned Date','DUE_DATE'=>'Due Date','COMMENT'=>'Description');
	
				$LO_ret = array(0=>array());
				foreach($assignments_RET as $assignment)
				{
							$LO_ret[] = array('TITLE'=>$assignment['TITLE'],'CATEGORY'=>$assignment['CATEGORY'],'ASSIGNED_DATE'=>$assignment['ASSIGNED_DATE'],'DUE_DATE'=>$assignment['DUE_DATE'],'COMMENT'=>html_entity_decode($assignment['COMMENT']));
				}
				DrawHeader('Subject - '.substr($course['TITLE'],strrpos(str_replace(' - ',' ^ ',$course['TITLE']),'^')));
	
				unset($LO_ret[0]);
				ListOutput($LO_ret,$LO_columns,'Assignment','Assignments',array(),array(),array('center'=>false,'save'=>$_REQUEST['id']!='all','search'=>false));
			}
			
        }
		

        break;
}

function _nl2br($value,$column)
{
    return nl2br($value);
}
function makeDescription($value,$column)
{
    return '<div style="width:450px;word-wrap:break-word;">'.$value.'</div>';
}
?>
