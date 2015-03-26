<?php

class STKnowledgeSearchController extends JsonController
{
    
    public function exec()
    {
        $searchString = Env::Post('searchString', Env::TYPE_STRING, '');
        $kbPage = Env::Post('kbPage', Env::TYPE_INT, 0);
        $tmpArr = explode('~',$searchString);
        $books = explode(',',$tmpArr[0]);
        
        $search = $tmpArr[1];
        
        $qp_model = new STQPFolderModel();

        /*foreach($kb as $row) {
            if($QPB_ID = $qp_model->getBookIdByTextId($row['id'])) {
                $knowledgeBase[] = $QPB_ID;
            }
        }*/
        
        $query = $qp_model->searchPages($search, $kbPage, $books);
        
        $searchResults = self::handleResult($query['rows'], 'QPF_TEXT', 'QPF_NAME', $search);
        $searchResults['count'] = $query['count'];
        
        $this->response = $searchResults;
	}
	
    public function handleResult($qr, $textColumnName, $subjColumnName, $searchString)
    {
        $totalWords = explode(' ', preg_replace('/ +/', ' ', trim($searchString)));

        $result = array();
        $result['rows'] = $result['subjects'] = array();

        $i = -1;
        foreach($qr as $row) {

            $text = MailParsers::formatLead($row[$textColumnName]);

            foreach($totalWords as $key) {
                if(!preg_match("/$key/ui", $row[$subjColumnName].$text)) {
                //if(stripos($row[$subjColumnName].$text, $key) === false) {
                    continue 2;
                }
            }

            if(++$i >= 20) {
                break;
            }

            $textLen = strlen($text);
            $sLen = strlen($searchString);

            $sentences = preg_split('/\r\n|\n|(\.\s+)/u', $text);

            foreach($sentences as $key=>$value)
            {
                if($sentences[$key] == '')
                    unset($sentences[$key]);
            }

            $sentence = reset($sentences);

            $res = '';
            $prevWord = '';
            $total = 0;
            $y = 0;

            while(true)
            {
                if(!$sentence)
                    break;

                $words = preg_split('/(\s+)/u', $sentence);

                $wordPos = 0;
                $temp = '';

                if($y != 0)
                    $res .= '. ';

                foreach($words as $word)
                {
                    $temp .= ' '.$word;
                    ++$wordPos;

                    if(self::searchedWord($word, $totalWords))
                    {
                        if($y == 0)
                        {
                            $res .= ($wordPos <= 8) ? ' '.$temp : '&nbsp; ...'.$word;
                            $y = 8;

                            $total += $wordPos;
                        }
                        else
                        {
                            $res .= ' '.$word;

                            ++$total;

                            if ( $word != $prevWord )
                                $y = 8;
                            else
                                --$y;
                        }
                        $prevWord = $word;
                    }
                    elseif($y > 0)
                    {
                        $res .= ' '.$word;
                        ++$total;

                        if(--$y == 0)
                            $res .= '... ';
                    }
                    if($total > 25) {
                        $res .= '... ';
                        break 2;
                    }
                }

                $sentence = next($sentences);
            }
            $tmpRow = array();
            $tmpRow['text'] = self::highlightText($totalWords, trim($res));
            $tmpRow['name'] = self::highlightText($totalWords, $row[$subjColumnName]);
            $tmpRow['QPB_ID'] = $row['QPB_ID'];
            $tmpRow['QPF_TEXTID'] = $row['QPF_TEXTID'];
            $tmpRow['QPF_NAME'] = $row['QPF_NAME'];

            $result['rows'][] = $tmpRow;
            
//            $result['subjects'][$row['STH_ID']] = 1;
        }
        $result['limit'] = 20;

        return $result;
    }

    private function searchedWord($word, $totalWords)
    {
        foreach($totalWords as $searched)
        {
            $searched = trim($searched);
            if($searched == '') {
                continue;
            }
            if(preg_match("/$searched/ui", $word)) {
            //if(stristr($word, $searched) != false) {
                return true;
            }
        }
        return false;
    }

    private function highlightText($totalWords, $text)
    {
        return preg_replace('/('.join('|', $totalWords).')/iu', '<span class="highlight">$1</span>', $text);
    }
	
}

?>