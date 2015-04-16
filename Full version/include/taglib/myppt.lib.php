<?php
/**
 * 站内新闻调用标签
 *
 * @version        $Id:mynews.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>站内新闻</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>站内新闻调用标签</description>
<demo>
{dede:mynews row='' titlelen=''/}
</demo>
<attributes>
    <iterm>row:调用站内新闻数</iterm> 
    <iterm>titlelen:新闻标题长度</iterm>
</attributes> 
>>dede>>*/
 
function lib_myppt(&$ctag,&$refObj)
{
    global $dsql,$envs;
    //属性处理
    $attlist="row|5,typeid|0,titlelen|";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
 
    $innertext = trim($ctag->GetInnerText());
    if(empty($innertext)) $innertext ="[field:title/]";
    if(empty($row)) $row=5;
    if(empty($titlelen)) $titlelen=30;
    $idsql = '';
    if(!empty($typeid))
    {
     $idsql = " WHERE typeid='{$typeid}'";
     }
    $dsql->SetQuery("SELECT * FROM #@__myppt $idsql ORDER BY orderid ASC LIMIT 0,$row");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field','[',']');
    $ctp->LoadSource($innertext);
    $revalue = '';
    $GLOBALS['autoindex'] = 0;

    while($row = $dsql->GetArray())
    {
       
        $GLOBALS['autoindex']++;
        foreach($ctp->CTags as $tagid=>$ctag){
            @$ctp->Assign($tagid,$row[$ctag->GetName()]);
        }
        $revalue .= $ctp->GetResult();
    }
    return $revalue;
}