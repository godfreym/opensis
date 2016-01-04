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
$menu['users']['admin'] = array(

						'users/Preferences.php'=>'Preferences',
                                                1=>'Report',
                                                'users/UserAdvancedReport.php'=>'Advanced Report',
                                                2=>'Users',
						'users/User.php'=>'User Info',
                                                3=>'Staff',
                                                'users/Staff.php'=>'Staff Info',
                                                'users/Staff.php&staff_id=new'=>'Add a Staff',
						4=>'Setup',
						'users/Profiles.php'=>'Profiles',
						'users/UserFields.php'=>'User Fields',
                                                'users/StaffFields.php'=>'Staff Fields',
//                                                'users/UploadUserPhoto.php'=>'Upload Staff\'s Photo',
//						'users/UploadUserPhoto.php?modfunc=edit'=>'Update Staff\'s Photo',
						5=>'Teacher Programs',
                                                
					);

$menu['users']['teacher'] = array(
						'users/Staff.php'=>'Staff Info',
						'users/Preferences.php'=>'Preferences'
					);

$menu['users']['parent'] = array(
						'users/User.php'=>'General Info',
						'users/Preferences.php'=>'Preferences'
					);

$exceptions['users'] = array(
						'users/User.php?staff_id=new'=>true
					);
?>