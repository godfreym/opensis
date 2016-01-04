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

include('RedirectRootInc.php');
include'ConfigInc.php';
include 'Warehouse.php';
$school_id=(int)$_GET['id'];
$res= DBGet(DBQuery('SELECT * FROM school_gradelevels WHERE school_id='.$school_id.''));

$period_select="<OPTION value=''>N/A</OPTION>";
foreach($res as $r1)
{
    $period_select .= "<OPTION value=$r1[ID]>$r1[TITLE]</OPTION>";
}

echo $period_select;

?>