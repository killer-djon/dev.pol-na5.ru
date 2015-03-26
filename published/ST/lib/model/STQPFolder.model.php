<?php

class STQPFolderModel extends DbModel
{
	protected $table = 'QPFOLDER';
	protected $book_table = 'QPBOOK';

	public function searchPages($searchString, $kbPage, $books, $limit = 20)
	{
		$booksIds = array();
        foreach ($books as $book){
            if (!empty($book)){
                $booksIds[] = "'". $this->escape($book)."'";
            }
        }
        $booksIds = implode(',', $booksIds);
        
		//$kb =join("' OR QPB_ID='", $knowledgeBase);

		$searchString = mysql_real_escape_string($searchString);
		$searchArray = explode(' ', preg_replace('/ +/', ' ', trim($searchString)));

		$str = array();
		foreach($searchArray as $key) {
			$str[] = "(QPF_NAME LIKE '%$key%' OR QPF_TEXT LIKE '%$key%')";
		}
		$str = join(' AND ', $str);

		$startIndex = $kbPage * $limit;
		$stopIndex = $limit + 1;

		$sql = "SELECT * FROM {$this->table} WHERE (QPB_ID IN ($booksIds)) AND ($str) AND QPF_PUBLISHED=1 ORDER BY QPB_ID, QPF_SORT LIMIT $startIndex, $stopIndex";

		return array(
		  'rows' => $this->prepare($sql)->query()->fetchAll(), 
		  'count' => $this->prepare("SELECT COUNT(*) FROM {$this->table} WHERE (QPB_ID IN ($booksIds)) AND ($str) AND QPF_PUBLISHED=1 ORDER BY QPB_ID, QPF_SORT")->query()->fetchField()
		);
	}

	public function getPage($QPF_TEXTID, $QPB_ID)
	{
		$sql = "SELECT * FROM {$this->table} WHERE QPF_TEXTID=s:QPF_TEXTID AND QPB_ID=s:QPB_ID AND QPF_PUBLISHED=1";
		return $this->prepare($sql)->query(array('QPF_TEXTID'=>$QPF_TEXTID, 'QPB_ID'=>$QPB_ID))->fetchAssoc();
	}

	public function getBookPages($QPB_TEXTID)
	{
		$sql = "SELECT QPB_ID, QPF_ID, QPF_TEXTID, QPF_NAME, QPF_SORT FROM {$this->table} WHERE QPB_ID =
			(SELECT QPB_ID FROM {$this->book_table} WHERE QPB_TEXTID=s:QPB_TEXTID LIMIT 1) AND QPF_PUBLISHED=1 ORDER BY QPB_ID";
		$result = $this->prepare($sql)->query(array('QPB_TEXTID'=>$QPB_TEXTID))->fetchAll();

		$levels = array();
		foreach($result as $key=>$row) {
			$ids = explode('.', $row['QPF_ID']);
			$level = count($ids) - 2;
			$levels[$level][$ids[$level]] = $row['QPF_SORT'];
			for($i=0; $i<=$level; $i++) {
				$ids[$i] = str_pad((string)$levels[$i][$ids[$i]], 10, ' ', STR_PAD_LEFT);
			}
			$row['sort'] = join('', $ids);
			$result[$key] = $row;
		}
		usort($result, array($this, 'sort_cmp'));
		return $result;
	}

	private function sort_cmp($a, $b)
	{
		if($a['sort'] == $b['sort']) {
			return 0;
		}
		return ($a['sort'] < $b['sort']) ? -1 : 1;
	}

	public function getBookIdByTextId($QPB_TEXTID)
	{
		$sql = "SELECT QPB_ID FROM {$this->book_table} WHERE QPB_TEXTID=s:QPB_TEXTID LIMIT 1";
		return $this->prepare($sql)->query(array('QPB_TEXTID'=>$QPB_TEXTID))->fetchField('QPB_ID');
	}

	public function setPage($data)
	{
		$data['QPB_ID'] = self::getBookIdByTextId($data['QPB_TEXTID']);

		if(!$data['QPB_ID']) {
			throw new Exception('Invalid book ID: '.$data['QPB_TEXTID']);
		}

		$sql = "SELECT QPF_ID FROM {$this->table} ORDER BY QPF_ID+0 DESC LIMIT 1";
		$folderID = $this->prepare($sql)->query($data)->fetchField('QPF_ID');

		$sql = "SELECT MAX(QPF_SORT) AS QPF_SORT FROM {$this->table} WHERE QPB_ID=s:QPB_ID";
		$data['QPF_SORT'] = $this->prepare($sql)->query($data)->fetchField('QPF_SORT') + 1;

		$data['QPF_ID'] = (preg_replace('/(\d)\..*/', '$1', $folderID) + 1).'.';
		$data['QPF_UNIQID'] = base64_encode(User::getId().'+'.time().'+'.$data['QPF_ID'].'+'.uniqid(rand(), true));

		$data['QPF_TEXTID'] = preg_replace('/[\s"\']/', '', strtolower($data['QPF_TITLE']));
		$data['QPF_NAME'] = $data['QPF_TITLE'];
		$data['QPF_CONTENT'] = nl2br($data['QPF_TEXT']);
		$data['QPF_MODIFYUSERNAME'] = User::getName();
		$data['QPF_ID_PARENT'] = 'ROOT';
		$data['QPF_STATUS'] = 0;

		$sql = "INSERT INTO {$this->table} SET
			QPF_ID = s:QPF_ID,
			QPB_ID = s:QPB_ID,
			QPF_TEXTID = s:QPF_TEXTID,
			QPF_NAME = s:QPF_NAME,
			QPF_TITLE = s:QPF_TITLE,
			QPF_KEYWORDS = '', QPF_DESCRIPTION = '',
			QPF_CONTENT = s:QPF_CONTENT,
			QPF_TEXT = s:QPF_TEXT,
			QPF_AUX1 = '', QPF_AUX2 = '', QPF_AUX3 = '',
			QPF_PUBLISHED = i:QPF_PUBLISHED,
			QPF_ATTACHMENT = '',
			QPF_MODIFYDATETIME = NOW(),
			QPF_MODIFYUSERNAME = s:QPF_MODIFYUSERNAME,
			QPF_ID_PARENT = s:QPF_ID_PARENT,
			QPF_STATUS = i:QPF_STATUS,
			QPF_SORT = i:QPF_SORT,
			QPF_UNIQID = s:QPF_UNIQID";
		
		return $this->prepare($sql)->query($data)->lastInsertId();
	}

}

?>