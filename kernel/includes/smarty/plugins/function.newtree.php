
<?php
function smarty_function_newtree($params, &$smarty){
    
    $disp='';
    $disp.='<ul class="menu vertical">';
    $sql='SELECT categoryID, slug, parent, '.LanguagesManager::sql_prepareField('name').' AS name from '.CATEGORIES_TABLE. ' where parent=1 order by name';
    if($r=mysql_query($sql))
        while($res=mysql_fetch_assoc($r)){
            $disp.='<li class="point'; 
            if($_GET['categoryID'] == $res['categoryID']) $disp.=' active';
            $disp.='"><a href="category/'.$res['slug'].'/">'.$res['name'].'</a>';
            $disp.= subcat($res['categoryID']) . "</li>";
        }
        
    $disp.='</ul>';
    return $disp;
}

function subcat($parid){
    $disp='';
    $sql='SELECT categoryID, slug, parent, '.LanguagesManager::sql_prepareField('name').' AS name from '.CATEGORIES_TABLE. ' where parent='.$parid.' order by sort_order';
    if($r=mysql_query($sql))
    	$disp .= '<ul class="sub-menu vertical">';
        while($res=mysql_fetch_assoc($r)){
            $disp.='<li class="point'; 
            if($_GET['categoryID'] == $res['categoryID']) $disp.=' active';
            $disp.='"><a href="category/'.$res['slug'].'/">'.$res['name'].'</a>';
            $disp.='</li>';
        }
         $disp.='</ul>';
    return $disp;
}

?>