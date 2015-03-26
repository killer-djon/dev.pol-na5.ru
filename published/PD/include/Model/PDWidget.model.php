<?php

    class PDWidget extends DbModel  
    {
        const TYPE_LINK = 'Link';
        const TYPE_WIDGET = 'Widget';
        
    	protected $table = 'WG_WIDGET';
    	
    	/**
    	 * @param array $images = [1,3,5,6]
    	 * @param int $type 
    	 */
    	public function addWidget($images, $mode, $link, $desc, $type = self::TYPE_LINK, $widget_type = null) 
    	{    	   

        	$isUnical = false;
            while (!$isUnical) {
                $rows = $this->prepare("SELECT * FROM WG_WIDGET WHERE WG_FPRINT = s:fprint")
                     ->query(array( 'fprint' => $link ));

                     
                if ( $rows->count() > 0 ) {
                    $link = mb_substr( $link, 0, strlen($link)-1  );
                }
                else
                    $isUnical = true;
                
            }
            
            if ( $type == self::TYPE_WIDGET ) {
                $type = $widget_type;
            }
    		
            $wgId = $this->prepare("INSERT INTO WG_WIDGET (WT_ID, WST_ID, WG_FPRINT, WG_DESC, WG_USER, WG_LANG, WG_CREATED_BY, WG_CREATED_DATETIME ) "
                  		        ."VALUE ('PDList', s:type, s:fprint, s:desc, s:user, s:lang, s:createby, s:time)")
                 ->query(array(
                     'fprint' => $link,
                     'type' => $type,
                     'desc' => $desc,
                     'user' => User::getId(),
                     'lang' => User::getLang(),
                     'createby' => User::getName(),
                     'time' => CDateTime::now()->toStr()
                 ))->lastInsertId();
                         
            $this->prepare("INSERT INTO WG_PARAM SET WG_ID = i:wgId, WGP_NAME = 'FILES', WGP_VALUE = s:images")
                 ->exec(array(
                     'wgId' => $wgId,
                     'images' => implode(',', $images)
                 ));
                 
            $this->prepare("INSERT INTO WG_PARAM SET WG_ID = i:wgId, WGP_NAME = 'VIEW_MODE', WGP_VALUE = s:mode")
                 ->exec(array(
                     'wgId' => $wgId,
                     'mode' => $mode
                 ));
            return $wgId;
    	}
    	
    	public function getAll()
    	{
    	    return $this->query("SELECT * FROM WG_WIDGET WHERE WT_ID = 'PDList' ORDER BY WG_CREATED_DATETIME DESC")->fetchAll();
    	}
    	
    	public function getById($id)
    	{
    	    $rows = $this->prepare("SELECT * FROM WG_PARAM WHERE WG_ID = i:id")
                        ->query(array(
    	                    'id' => $id
    	                ))->fetchAll();
            $param = array();
    	    foreach ($rows as $row) {
    	        $param[ $row['WGP_NAME'] ] = $row['WGP_VALUE'];
    	    }
    	    
    	    $widget = $this->prepare("SELECT * FROM WG_WIDGET WHERE WG_ID = i:id")
    	                ->query(array(
    	                    'id' => $id
    	                ))->fetchAssoc();

    	                
            return array(
                'param' => $param,
                'widget' => $widget
            );
    	                
    	}
    }
	

?>