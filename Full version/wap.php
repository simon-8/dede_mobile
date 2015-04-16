<?php
/**
** Design by Simon 2015-03-26 
** Simon8.com
**/
require_once (dirname(__FILE__) . "/include/common.inc.php");
$cfg_templets_dir = $cfg_basedir.$cfg_templets_dir;
$channellist = '';
$newartlist = '';
$channellistnext = '';
$dsql->Execute();
$pre_= get_config1("prefix"); //获得当前表前缀
$about_id = get_config1("about");//关于我们id
if(isset($typeid)){
	// header('/wap.php');
	$type = $dsql->GetOne("SELECT * FROM ".$pre_."arctype WHERE id=".$typeid);
	$channel = $type['channeltype'];//模型id
	$typename = $type['typename'];
	$action = $dsql->GetOne("SELECT * FROM ".$pre_."channeltype WHERE id=".$channel);
	$action = $action['nid'];//模型类型  可以通过模型确定要输出的样式

	switch ($typeid) {//特定情况
		case '88':
			$action = 'contact';//联系我们
			break;
		case '99':
			$action = 'tools';//使用工具
			break;
		case $about_id:
			$action = 'about';//关于我们
			break;
		case '100':
			$action = 'fenxiang';//分享页面
			break;
		case '1000':
			$action = 'map';//地图
			break;
		default:
			# code...
			break;
	}
	//设置各种模板
}else{
	$action = "index";
}
// ---------------------通用信息---------------------
$skin = '/templets/wap/';//设置模板路径
$nopic = '/images/defaultpic.gif';
$curtime = strftime("%Y-%m-%d %H:%M:%S",time());//当前时间
$mobile = get_config1('mobile');//联系电话
$icp = get_config('cfg_beian');//备案号
$webname = get_config('cfg_webname');//网站名称
$company = get_config1('company');//公司名称
$description = get_config('cfg_description');//站点描述
$address = get_config1('address');//公司地址
$email = get_config1('email');//公司邮箱
$link = "/wap.php?typeid=";
$typearr = get_config1("typeid");
$side_cate = get_query("SELECT id,typename FROM ".$pre_."arctype WHERE id IN ($typearr)");
$side_cate[] = array('id'=>'88','typename'=>'联系我们');
$side_cate[] = array('id'=>'99','typename'=>'实用工具');
$page_art = 6;
$page_shop = 9;
$tag = array('span','b');//去除内容标签
/*$config = $dsql->GetOne("SELECT * FROM dede_wapconfig WHERE var='pagesize'");
$pagesize = $config['value'];//分页数量*/
// --------------------------------------------------

if($action == 'index'){  //首页
	//首页幻灯片
	$flash = get_query("SELECT * FROM dede_myppt WHERE typeid='2' ORDER BY orderid ASC"); 
 	//首页新闻
 	$news_id = get_config1('news');
 	$newsarr = cat_child($news_id);
	$news = get_query("SELECT * FROM ".$pre_."archives WHERE typeid IN ($newsarr) AND arcrank='0' ORDER BY id DESC LIMIT 0,5"); 
	//首页栏目
/*	$cate = get_query("SELECT a.id,a.typename,b.typeid,b.typelitpic,b.listorder FROM ".$pre_."arctype AS a LEFT JOIN dede_waptype AS b ON a.id=b.typeid WHERE b.mobile=1 ORDER BY b.listorder asc");*/
	$cate1 = get_query("SELECT a.typeid AS id,a.mobile,b.typeid,b.typelitpic,b.listorder FROM dede_wapchannel AS a LEFT JOIN dede_waptype AS b ON a.typeid=b.typeid WHERE a.mobile=1");
	$cate = array();
	foreach ($cate1 as $key => $value) {
		$value['typename'] = get_typename($value['id']);
		$value['typelitpic'] = $value['typelitpic'] == '' ? $nopic : $value['typelitpic'] ;
		$cate[] = $value;
	}
	// print_r($cate);
	//追加contact和tools
	$contact = array();
	$contact['typelitpic'] = get_pic(88);
	$contact['id'] = 88;
	$contact['mobile'] = 1;
	$contact['typename'] = '联系我们';
	$contact['listorder'] = get_value(88);
	$cate[] = $contact;
	$tools = array();
	$tools['typelitpic'] = get_pic(99);
	$tools['id'] = 99;
	$tools['mobile'] = 1;
	$tools['typename'] = '实用工具';
	$tools['listorder'] = get_value(99);
	$cate[] = $tools;
	$cate = order($cate,'listorder');
	// print_r($cate);
	//引入模版
	include($cfg_templets_dir."/wap/index.html");
	exit();

}else if($action == 'article'){ //文章

	$category = cat_brother($typeid);//最大父分类下的子分类

	if(!empty($_GET['id'])){//具体文章
		$id = (int)abs($_GET['id']);
		$article = $dsql->GetOne("SELECT * FROM ".$pre_."archives WHERE id=$id");
		$click = $article['click'];
		$addtime = $article['sortrank'];
		$title = $article['title'];
		$cont = $dsql->GetOne("SELECT * FROM ".$pre_."addonarticle WHERE aid=$id");
		$content = str_tags($cont['body'],$tag);
		// $redirect = $cont['redirecturl'];
		$prev = get_other('prev',$typeid,$id);//上一篇
		$next = get_other('next',$typeid,$id);//下一篇
		include($cfg_templets_dir."/wap/article-show.html");
	}else{//文章首页列表
		$page = isset($_GET['page']) ? (int)abs($_GET['page']) : 1;//检查分页
		$pagearray = page($typeid,$page,$page_art);
		$na = $pagearray['n'];
		$pa = $pagearray['p'];
		$limit = 0+($page-1)*$page_art.",".$page_art;
		$sql = "SELECT * FROM ".$pre_."archives WHERE typeid IN (".cat_child($typeid).") AND arcrank='0' ORDER BY id DESC LIMIT $limit";
		$list = get_query($sql);
		// print_r(cat_child($typeid));
		$count = get_count($typeid);
		include($cfg_templets_dir."/wap/article.html");
	}
	exit();

}else if($action == 'shop'){ //商品

	$category = cat_brother($typeid);//最大父分类下的子分类
	if(!empty($id)){
		$id = (int)abs($_GET['id']);
		$article = $dsql->GetOne("SELECT * FROM ".$pre_."archives WHERE id=$id");
		$title = $article['title'];
		$content = $dsql->GetOne("SELECT * FROM ".$pre_."addonshop WHERE aid=$id");
		$content = str_tags($content['body'],$tag);
		$prev = get_other('prev',$typeid,$id);
		$next = get_other('next',$typeid,$id);
		include($cfg_templets_dir."/wap/shop-show.html");
	
	}else{
		$page = isset($_GET['page']) ? (int)abs($_GET['page']) : 1;//检查分页
		$pagearray = page($typeid,$page,$page_shop);
		$na = $pagearray['n'];
		$pa = $pagearray['p'];
		$limit = 0+($page-1)*$page_shop.",".$page_shop;
		$sql = "SELECT * FROM ".$pre_."archives WHERE  typeid IN (".cat_child($typeid).") AND arcrank='0' LIMIT $limit";
		$list = get_query($sql);
		$count = get_count($typeid);
		include($cfg_templets_dir."/wap/shop.html");		
	}
		exit();

}else if($action == 'image'){ //图片集
	$category = cat_brother($typeid);//最大父分类下的子分类
	if(!empty($id)){
		$id = (int)abs($_GET['id']);
		$article = $dsql->GetOne("SELECT * FROM ".$pre_."archives WHERE id=$id");
		$title = $article['title'];
		$content = $dsql->GetOne("SELECT * FROM ".$pre_."addonimages WHERE aid=$id");
		$content = str_tags($content['body'],$tag);
		$images = getimg($id);
		$prev = get_other('prev',$typeid,$id);
		$next = get_other('next',$typeid,$id);
		// var_dump();
		include($cfg_templets_dir."/wap/images-show.html");
	
	}else{
		$page = isset($_GET['page']) ? (int)abs($_GET['page']) : 1;//检查分页
		$pagearray = page($typeid,$page,$page_shop);
		$na = $pagearray['n'];
		$pa = $pagearray['p'];
		$limit = 0+($page-1)*$page_shop.",".$page_shop;
		$sql = "SELECT * FROM ".$pre_."archives WHERE  typeid IN (".cat_child($typeid).") AND arcrank='0' LIMIT $limit";
		$list = get_query($sql);
		$count = get_count($typeid);
		include($cfg_templets_dir."/wap/images.html");		
	}
		exit();
}else if($action == 'about'){  //关于我们
/*
if(!empty($id)){
			$article = $dsql->GetOne("SELECT a.id,a.title,a.typeid,b.aid,b.body FROM ".$pre_."archives as a LEFT JOIN ".$pre_."addonarticle as b ON a.id=b.aid  WHERE a.typeid=".$typeid." AND a.id=".$id);
		}else{
			$article = $dsql->GetOne("SELECT a.id,a.title,a.typeid,b.aid,b.body FROM ".$pre_."archives as a LEFT JOIN ".$pre_."addonarticle as b ON a.id=b.aid  WHERE a.typeid=".$typeid." ORDER BY id asc");
			$id = $article['id'];
		}
		// var_dump($article);
		$title = $article['title'];
		$content = str_tags($article['body'],$tag);
*/
	if(!empty($id)){
		$cont = $dsql->GetOne("SELECT typename,content FROM `#@__arctype` WHERE id=".$id);
		$title = $cont['typename'];
		$content = $cont['content'];
	}else{
		$content = $dsql->GetOne("SELECT content FROM `#@__arctype` WHERE id=".$about_id);
		$content = $content['content'];
	}
	$other1 = get_query("SELECT id,typename,content FROM ".$pre_."arctype WHERE reid=".$about_id);
	$other = array();
	foreach ($other1 as $key => $value) {
		if(strlen($value['content']) > 10 ){
			$other[] = $value;	
		}
		
	}
	include($cfg_templets_dir."/wap/about.html");	

}else if($action == 'contact'){//联系我们

	include($cfg_templets_dir."/wap/contact.html");

}else if($action == 'tools'){  //实用工具

		include($cfg_templets_dir."/wap/tools.html");

}else if($action == 'fenxiang'){ //我的分享

		include($cfg_templets_dir."/wap/fenxiang.html");
}else if($action == 'map'){
		$map = get_config1('map');
		include($cfg_templets_dir."/wap/map.html");
}


/*------------------------------------------------------------------------------------------------*/
//查找变量信息  varname是变量名   prefix是系统表前缀,默认dede
function get_config($varname,$prefix='dede'){
		global $pre_;
		$sql = "SELECT * FROM ".$pre_."sysconfig WHERE varname='".$varname."'"; 
		$result = mysql_query($sql);
		$result1 = mysql_fetch_assoc($result);
		return $result1['value'];
}
//读取配置值
function get_config1($varname){
	$sql = "SELECT value FROM dede_wapconfig WHERE var='$varname'";
	$result = mysql_query($sql);
	$result1 = mysql_fetch_assoc($result);
	$value = $result1['value'];
	return $value;
}
// 批处理 返回数组
function get_query($sql){
		$result = mysql_query($sql);
		$list = array();
		while($result1 = mysql_fetch_assoc($result)){
			$list[] = $result1;
		}
		return $list;
}
//上一篇下一篇
function get_other($action,$typeid,$id){
		global $dsql,$link;
		$child = cat_child($typeid);//当前分类下所有分类
		if($action == 'next'){
       			 $result = $dsql->GetOne("Select id,title From `#@__archives` where id>$id And arcrank>-1 AND typeid IN (".$child.") order by id asc");//下一篇
		}else if($action == 'prev'){
      		     $result = $dsql->GetOne("Select id,title From `#@__archives` where id<$id And arcrank>-1 AND typeid IN (".$child.") order by id desc");//上一篇			
		}
		if(empty($result)){
			$result['title'] = '没有了';
			$result['url'] = '#';
		}else{
			$result['url'] = $link.$typeid.'&id='.$result['id'];
		}
		return $result;
}
//得到上一页下一页数据
function page($typeid,$page,$pagesize){
		global $link,$pre_;
		$condition = " typeid IN (".cat_child($typeid).") AND arcrank='0'"; // 当前查找条件
		$count = get_query("SELECT * FROM ".$pre_."archives WHERE ".$condition);
		if(count($count) < $pagesize){//如果数量小于分页标准  页数=1
			$pages = 1;
		}else{//有多少页
			$pages = count($count)%$pagesize == 0 ? (int)(count($count)/$pagesize) : (int)(count($count)/$pagesize)+1;
		}
		if($page > $pages) $page = $pages;//如果当前页面超过总页数 , 算最后页
		$next = ($page+1) > $pages ? false : $page+1;
		$prev = ($page-1) < 1 ? false : $page-1;
		$pagearray['n'] = $next ? "<a href='".$link.$typeid."&page=".$next."'>下一页</a>" : "<a href=''>没有了</a>";
		$pagearray['p'] = $prev ? "<a href='".$link.$typeid."&page=".$prev."'>上一页</a>" : "<a href=''>第一页</a>";
		return $pagearray;
}
//当前分类的父分类 , 并输出当前父分类所有二级子分类
function cat_brother($typeid){
	global $pre_;
	$sql = "SELECT id,topid FROM ".$pre_."arctype WHERE id=".$typeid;
	$result = mysql_query($sql);
	$result1 = mysql_fetch_assoc($result);
	$topid = $result1['topid'] == 0 ? $result1['id'] : $result1['topid'] ;//顶级id
	//该顶级id下的直属分类
	$sql = "SELECT id,reid,topid,typename FROM ".$pre_."arctype WHERE reid=topid AND reid=".$topid;
	$result = mysql_query($sql);
	while($result1 = mysql_fetch_assoc($result)){
		$list[] = $result1;
	}
	return $list;
}
//返回该typeid所有子分类
function cat_child($typeid){
	global $pre_;
	$sql = "SELECT id FROM ".$pre_."arctype WHERE reid=".$typeid." OR topid=".$typeid;
	$result = mysql_query($sql);
	$childarr = array();
	while($result1 = mysql_fetch_assoc($result)){
		$childarr[] = $result1['id'];
	}
	if(!count($childarr)){//没有子分类
		$child = $typeid;
	}else{
		$child = implode(',',$childarr);//字符串形式 方便WHERE条件 typeid IN ($child)查找
		$child = $child.",".$typeid;//把自己加进去			
	}
	return $child;//返回 
}
function get_count($typeid){
	global $pre_;
	$child = cat_child($typeid);
	$result = mysql_query("SELECT count(*) as num FROM ".$pre_."archives WHERE typeid IN (".$child.") AND arcrank='0'");
	$result1 = mysql_fetch_assoc($result);
	$count = '';
	$count = $result1['num'];
	return $count;
}

function get_pic($typeid){
	$sql = "SELECT typeid,typelitpic FROM dede_waptype WHERE typeid=".$typeid;
	$result = mysql_query($sql);
	$result1 = mysql_fetch_assoc($result);
	$value = $result1['typelitpic'];
	return $value;
}
function get_value($typeid){
	$sql = "SELECT listorder FROM dede_waptype WHERE typeid=".$typeid;
	$result = mysql_query($sql);
	$result1 = mysql_fetch_assoc($result);
	$value = $result1['listorder'];
	return $value;
}
function get_typename($typeid){
	global $pre_;
	$sql = "SELECT * FROM ".$pre_."arctype WHERE id=".$typeid;
	$result = mysql_query($sql);
	$result1 = mysql_fetch_assoc($result);
	$value = $result1['typename'];
	return $value;
}
//二维数组按指定字段顺序排序
function order($array,$zd){
	//设置个arr
	$arr = array();
	foreach ( $array as $key => $value ) {
/*----------------判断是不是数组------*/
		if( is_array($value )){
			
			if( $arr[$value[$zd]] != '' ){
				$k = $value[$zd]+1;//避免重复
				$arr[$k] = $value;
			}else{
				//把排序的字段 分给$arr做键  方便排序
				$arr[$value[$zd]] = $value;				
			}
		}
/*----------------判断是不是数组------*/
	}

			//arr组建完毕 , 开始排序
			ksort($arr);
	return $arr;
}

function getimg($aid)  
{  
global $dsql;
$imgurls = '';
$row = $dsql -> GetOne("Select * From`#@__addonimages` where aid='$aid'"); //
$imgurls = $row['imgurls'];
preg_match_all("/{dede:img (.*)}(.*){\/dede:img/isU", $imgurls, $wordcount);
$count = count($wordcount[2]);
if ($num > $count || $num == 0){
$num = $count;
}
for($i = 0;$i < $num;$i++){
	$imglist[] = trim($wordcount[2][$i]);
}
return $imglist;
}

function str_tags($text, $tags = array())
   {
       $args = func_get_args();
       $text = array_shift($args);
       $tags = func_num_args() > 2 ? array_diff($args,array($text))  : (array)$tags;
       foreach ($tags as $tag){
           if(preg_match_all('/<'.$tag.'[^>]*>(.*)<\/'.$tag.'>/iU', $text, $found)){
               $text = str_replace($found[0],$found[1],$text);
         }
       }
       return $text;
   }
?>