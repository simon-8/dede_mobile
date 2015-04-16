<?php
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/datalistcp.class.php');
require_once(DEDEINC.'/common.func.php');
setcookie('ENV_GOBACK_URL',$dedeNowurl,time()+3600,'/');
function GetPptTypeName($id)
{
global $dsql;
$row = $dsql->GetOne("Select * From `#@__myppttype` where id='$id'");
$typename=$row['typename'];
if($typename=="")
{
$typename="默认分类";
}
return  $typename;
}
if(!empty($tid))
{
	$sql = "Select * from `#@__myppt` where typeid='{$tid}'  order by orderid asc";
}
else
{
$sql = "Select * from `#@__myppt`  order by aid desc";
}

$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/ppt_main.htm");
$dlist->SetSource($sql);
$dlist->display();

function TestType($tname)
{
	if($tname=="")
	{
		return "所有栏目";
	}
	else
	{
		return $tname;
	}
}

function TimeSetValue($ts)
{
	if($ts==0)
	{
		return "不限时间";
	}
	else
	{
		return "限时标记";
	}
}

?>