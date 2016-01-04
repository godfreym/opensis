function hide_search_div() 
{ 
	document.getElementById("searchdiv").style.display = "none";
	document.getElementById("addiv").style.display = "block";
} 

function show_search_div() 
{ 
	document.getElementById("searchdiv").style.display = "block";
	document.getElementById("addiv").style.display = "none";
} 

function hidediv() 
{ 
	if (document.getElementById) 
	{ 
		document.getElementById("hideShow").style.display = "none";
	} 
} 

function showdiv() 
{ 
	if (document.getElementById) 
	{ 
		document.getElementById("hideShow").style.display = "block";
	} 
} 

function prim_hidediv() 
{ 
	if (document.getElementById) 
	{ 
		document.getElementById("prim_hideShow").style.display = "none";
	} 
} 

function prim_showdiv() 
{ 
	if (document.getElementById) 
	{ 
		document.getElementById("prim_hideShow").style.display = "block";
	} 
} 

function sec_hidediv() 
{ 
	if (document.getElementById) 
	{ 
		document.getElementById("sec_hideShow").style.display = "none";
	} 
} 

function sec_showdiv() 
{ 
	if (document.getElementById) 
	{ 
		document.getElementById("sec_hideShow").style.display = "block";
	} 
} 

function addn_hidediv() 
{ 
	if (document.getElementById) 
	{ 
		document.getElementById("addn_hideShow").style.display = "none";
	} 
} 

function addn_showdiv() 
{ 
	if (document.getElementById) 
	{ 
		document.getElementById("addn_hideShow").style.display = "block";
	} 
}
function confirmAction(){
    chk='n';
    var option="";
if(document.run_schedule.test_mode.checked==false)
{
    
    if(document.run_schedule.delete_mode.checked==false)
        {
            
            chk='y';
        }
        else
            var option="delete current schedules ? ";
}
else
    var option="run the scheduler to schedule unscheduled requests? ";
if(chk=='y')
{
    var d = $('divErr');
    var err = "Please select one options.";
    d.innerHTML="<b><font color=red>"+err+"</font></b>";
    return false;
}
else
{
      if (confirm("Do you really want to "+option) == true)
         return true;
      else
         return false;
}
  }

function showhidediv(it,box)
{
	if (document.getElementById)
	{



                var vis = (box.checked) ? "block" : "none";

		document.getElementById(it).style.display = vis;
	}
}

function system_wide(val)
{
    var check_id='all_day_'+val;
    if(document.getElementById(check_id).checked == false)
    {
        document.getElementById('syswide_holi_'+val).style.display="block";
    }
    else
    {
        document.getElementById('syswide_holi_'+val).style.display="none";
    }
}
function show_this_msg(tag_id,msg_id,option)
{
    document.getElementById(tag_id).disabled=true;

    if(option=="calendar")
        option="the calendar";
    if(option=="grade")
        option="the grade scale";
    document.getElementById(msg_id).innerHTML='<font style="color:red"><b>Cannot change '+option+' as this course period has association</b></font>';
}
function show_home_error()
{
    document.getElementById('divErr').innerHTML="<b><font color=red>Please provide home address first.</font></b>";
}
function set_check_value(val,name)
{
        if(val.checked==false)
            {
                
        document.getElementById(name).value='N';    
         document.getElementById('IS_EMERGENCY_HIDDEN').value="N";
            }
            else
                 document.getElementById('IS_EMERGENCY_HIDDEN').value="Y";

}
function portal_toggle(id)
{
    
    if(document.getElementById('portal_'+id).checked==true)
    {
    document.getElementById('portal_div_'+id).style.display="block";
    document.getElementById('portal_hidden_div_'+id).innerHTML='';
    }
    if(document.getElementById('portal_'+id).checked==false)
    {
    document.getElementById('portal_div_'+id).style.display="none";
    if(id=='1')
    document.getElementById('portal_hidden_div_'+id).innerHTML='<input type=hidden name="values[student_contacts][PRIMARY][USER_NAME]" value=""/><input type=hidden name="values[student_contacts][PRIMARY][PASSWORD]" value=""/>';
    if(id=='2')
    document.getElementById('portal_hidden_div_'+id).innerHTML='<input type=hidden name="values[student_contacts][SECONDARY][USER_NAME]" value=""/><input type=hidden name="values[student_contacts][SECONDARY][PASSWORD]" value=""/>';
    }
}
function show_span(id,val)
{
    if(val=='Y')
    document.getElementById(id).style.display="block";
    if(val=='N')
    document.getElementById(id).style.display="none";
}
function show_cc()
{
	if(document.getElementById('cc').style.display=='none')
	document.getElementById('cc').style.display='block';
	else
	document.getElementById('cc').style.display='none'
	
}
function show_bcc()
{
	if(document.getElementById('bcc').style.display=='none')
	document.getElementById('bcc').style.display='block';
	else
	document.getElementById('bcc').style.display='none'
	
}

function attachfile()
{
    if(document.getElementById('attach1').style.display=='none')
    {
	document.getElementById('attach1').style.display='block';
        document.getElementById('del1').style.display='block';
    }
	else
        {
	document.getElementById('attach1').style.display='none';
        document.getElementById('del1').style.display='none'
    }
}
function clearfile1()
{
    var oFiles = document.getElementById("up1");
    
    oFiles.value="";
   document.getElementById('del1').style.display='none';
   document.getElementById('attach1').style.display='none';
}
        
function attachanotherfile()
{
    if(document.getElementById('attach2').style.display=='none')
    {
	document.getElementById('attach2').style.display='block';
         document.getElementById('del2').style.display='block';
    }
	else
        {
	document.getElementById('attach2').style.display='none';
          document.getElementById('del2').style.display='none'
    }
}
function clearfile2()
{
    var oFiles = document.getElementById("up2");
    
    oFiles.value="";
   document.getElementById('del2').style.display='none';
   document.getElementById('attach2').style.display='none';
}

function cp_toggle(chk)
{
    if(chk.checked==true)
    {
        document.getElementById(chk.id +'_period').disabled=false;

        document.getElementById(chk.id +'_room').disabled=false;
        document.getElementById(chk.id +'_does_attendance').disabled=false;

    }
    else
    {
        document.getElementById(chk.id +'_period').value='';
        document.getElementById(chk.id +'_period').disabled=true;
        document.getElementById(chk.id+'_period_time').innerHTML='';
        document.getElementById(chk.id +'_room').value='';
        document.getElementById(chk.id +'_room').disabled=true;
        document.getElementById(chk.id +'_does_attendance').checked=false;
        document.getElementById(chk.id +'_does_attendance').disabled=true;
    }
}

function mp_range_toggle(rad)
{
    if(rad.checked==true && rad.id=='preset')
    {
        document.getElementById("mp_range").style.display='block';
        document.getElementById("date_range").style.display='none';
        document.getElementById("select_range").style.display='block';
        document.getElementById("select_mp").style.display='none';
    }
    else
    {
        document.getElementById("mp_range").style.display='none';
        document.getElementById("date_range").style.display='block';
        document.getElementById("select_range").style.display='none';
        document.getElementById("select_mp").style.display='block';
    }
}

function reset_schedule()
{
    document.getElementById("meeting_days").innerHTML='';
    document.getElementById("fixed_schedule").checked=false;
    document.getElementById("variable_schedule").checked=false;
    document.getElementById("blocked_schedule").checked=false;
    
}
function show_this_msg(tag_id,msg_id,option)
{
    document.getElementById(tag_id).disabled=true;

    if(option=="calendar")
        option="the calendar";
    if(option=="grade")
        option="the grade scale";
    document.getElementById(msg_id).innerHTML='<font style="color:red"><b>Cannot change '+option+' as this course period has association</b></font>';
}

function showDiv()
{ 
	if (document.getElementById) 
	{ 
		document.getElementById("attach").style.display = "block";
	} 
} 
function showDiv1()
{ 
	if (document.getElementById) 
	{ 
		document.getElementById("attach1").style.display = "block";
	} 
} 
function show_this_msg(msg) 
{ 
 document.getElementById('divErr').innerHTML= "<font style='color:red'>"+msg+"</font>";	
} 