<?php
/*********************************
 * URL установки Shop-Script
 * Для запуска из cron -- написать
 * полностью вместе с последним
 * слэшем
 *********************************/
define('BASE_URL', 'http://pol-na5.com/');

/*********************************
 * Куда записывать файлы sitemap
 *********************************/
define('SITEMAP_DIR2SAVE', 'publicdata/POLNA5/attachments/SC/sitemap/index.xml');

/*********************************
 * URL файлов с sitemap
 * Для запуска из cron -- написать
 * полностью вместе с последним
 * слэшем
 *********************************/
define('SITEMAP_URL2SAVE', 'http://www.pol-na5.ru/');

/*********************************
 * Количество URL-ов на один файл sitemap
 * общее количество страниц в одном файле не должно
 * превышать 50 тыс.
 *********************************/
define('ITEMS_PER_FILE', 45000);

/*********************************
 * Индексный файл sitemap в который
 * будут добавлены наши файлы
 *********************************/
define('SITEMAP_INDEX_SOURCE', SITEMAP_DIR2SAVE . DIRECTORY_SEPARATOR . 'sitemap_index_src.xml');

/*********************************
 * Индексный файл sitemap 
 * с добавленными файлами
 *********************************/
define('SITEMAP_INDEX_FILE', SITEMAP_DIR2SAVE . DIRECTORY_SEPARATOR . 'sitemap_index.xml');

/*********************************
 * Приоритет страниц AUX
 * NULL - оставить по-умолчанию
 *********************************/
define('AUX_PAGES_PRIORITY', NULL);

/*********************************
 * Частота изменения страниц AUX
 *********************************/
define('AUX_PAGES_CHANGEFREQ', 'monthly');

/*********************************
 * Генерировать URL с обсуждением
 * продуктов
 *********************************/
define('MAKE_DISCUSS_PAGES', true);

/*********************************
 * Приоритет страниц с товаром
 * NULL - оставить по-умолчанию
 *********************************/
define('PRODUCT_PAGE_PRIORITY', '0.9');

/*********************************
 * Частота изменения страниц с товаром
 *********************************/
define('PRODUCT_PAGE_CHANGEFREQ', 'daily');

/*********************************
 * Приоритет страниц с обсуждением товара
 * NULL - оставить по-умолчанию
 *********************************/
define('DISCUSS_PAGE_PRIORITY', '0.3');

/*********************************
 * Частота изменения страниц обсуждения
 * товара
 *********************************/
define('DISCUSS_PAGE_CHANGEFREQ', 'monthly');

/*********************************
 * Приоритет страниц AUX
 * NULL - оставить по-умолчанию
 *********************************/
define('CATEGORIES_PAGES_PRIORITY', NULL);

/*********************************
 * Частота изменения страниц AUX
 *********************************/
define('CATEGORIES_PAGES_CHANGEFREQ', 'monthly');

/*********************************
 * Пустой файл sitemap
 *********************************/
$sitemap_string = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
</urlset>
XML;

$sitemap_index_src = file_get_contents(SITEMAP_INDEX_SOURCE);

include("cfg/connect.inc.php");
include("cfg/tables.inc.php");
include("includes/database/mysql.php");

db_connect(DB_HOST,DB_USER,DB_PASS) or die (db_error());
db_select_db(DB_NAME) or die (db_error());

$sitemap_index = new SimpleXMLElement($sitemap_index_src);

/**********************************
 * Process AUX pages
 **********************************/
$q = db_query('SELECT COUNT(*) AS cnt FROM ' . AUX_PAGES_TABLE);
$res = db_fetch_row($q);
$item_count = $res['cnt'];

for ($i = 0; $i <= $item_count/ITEMS_PER_FILE; $i++) {
	$sitemap = new SimpleXMLElement($sitemap_string);
	$q = db_query('SELECT aux_page_ID AS id FROM '. AUX_PAGES_TABLE . ' LIMIT ' . $i*ITEMS_PER_FILE . ', ' . ITEMS_PER_FILE);
	while ($row = db_fetch_row($q)) {
		$url = $sitemap->addChild('url');
		$url->addChild('loc', BASE_URL . "index.php?show_aux_page={$row['id']}");
		$url->addChild('lastmod', date("c"));
		$url->addChild('changefreq', AUX_PAGES_CHANGEFREQ);
		if (!is_null(AUX_PAGES_PRIORITY)) {
			$url->addChild('priority', AUX_PAGES_PRIORITY);
		}
	}
	write_GZip (SITEMAP_DIR2SAVE . DIRECTORY_SEPARATOR . "sitemap_aux_$i.xml.gz", $sitemap->asXML());
	$sm = $sitemap_index->addChild('sitemap');
	$sm->addChild('loc', SITEMAP_URL2SAVE . "sitemap_aux_$i.xml.gz");
	$sm->addChild('lastmod', date("c"));
}

/**********************************
 * Process Products
 **********************************/	
$q = db_query('SELECT COUNT(*) AS cnt FROM '. PRODUCTS_TABLE . ' WHERE categoryID > 1');
$res = db_fetch_row($q);
$item_count = $res['cnt'];

// Если надо генерировать url с обсуждениями, то количество
// поделить на 2 -- на каждый продукт по 2 url получается
$items_per_file = ITEMS_PER_FILE / (MAKE_DISCUSS_PAGES ? 2 : 1);

for ($i = 0; $i <= $item_count/$items_per_file; $i++) {
	$sitemap = new SimpleXMLElement($sitemap_string);
	$q = db_query('SELECT productID AS id, date_modified as dm FROM '. PRODUCTS_TABLE . " WHERE categoryID > 1 LIMIT " . $i*$items_per_file . ", $items_per_file" );
	while ($row = db_fetch_row($q)) {
			$url = $sitemap->addChild('url');
			$url->addChild('loc', BASE_URL . "index.php?productID={$row['id']}");
			$url->addChild('lastmod', date("c", strtotime($row['dm'])));
			$url->addChild('changefreq', PRODUCT_PAGE_CHANGEFREQ);
			$url->addChild('priority', PRODUCT_PAGE_PRIORITY);
			
			if (MAKE_DISCUSS_PAGES) {
				$url = $sitemap->addChild('url');
				$url->addChild('loc', BASE_URL . "index.php?productID={$row['id']}&amp;discuss=yes");
				$url->addChild('lastmod', date("c", strtotime($row['dm'])));
				$url->addChild('changefreq', DISCUSS_PAGE_CHANGEFREQ);
				$url->addChild('priority', DISCUSS_PAGE_PRIORITY);
			}
	}
	
	write_GZip ( SITEMAP_DIR2SAVE . DIRECTORY_SEPARATOR . "sitemap_products_$i.xml.gz",	$sitemap->asXML());
	
	$sm = $sitemap_index->addChild('sitemap');
	$sm->addChild('loc', SITEMAP_URL2SAVE . "sitemap_products_$i.xml.gz");
	$sm->addChild('lastmod', date("c"));
}
	
/**********************************
 * Process Categories
 **********************************/	
 
$q = db_query('SELECT COUNT(*) AS cnt FROM '. CATEGORIES_TABLE . ' WHERE categoryID > 1');
$res = db_fetch_row($q);
$item_count = $row['cnt'];
	
for ($i = 0; $i <= $item_count/ITEMS_PER_FILE; $i++) {

	$q = db_query('SELECT categoryID AS id FROM '. CATEGORIES_TABLE . " WHERE categoryID > 1 LIMIT " . $i*ITEMS_PER_FILE . ', ' . ITEMS_PER_FILE );
		
	$sitemap = new SimpleXMLElement($sitemap_string);
		
	while ($row = db_fetch_row($q)) {
		$url = $sitemap->addChild('url');
		$url->addChild('loc', BASE_URL . "index.php?categoryID={$row['id']}");
		$url->addChild('lastmod', date("c"));
		$url->addChild('changefreq', CATEGORIES_PAGES_CHANGEFREQ);
		if (!is_null(CATEGORIES_PAGES_PRIORITY)) {
			$url->addChild('priority', CATEGORIES_PAGES_PRIORITY);
		}
	}
	write_GZip (SITEMAP_DIR2SAVE . DIRECTORY_SEPARATOR . "sitemap_categories_$i.xml.gz", $sitemap->asXML());
	$sm = $sitemap_index->addChild('sitemap');
	$sm->addChild('loc', SITEMAP_URL2SAVE . "sitemap_categories_$i.xml.gz");
	$sm->addChild('lastmod', date("c"));
}

file_put_contents(SITEMAP_INDEX_FILE, $sitemap_index->asXML());

function write_GZip ($filename, $content) {

	if (!($gf = gzopen($filename, 'wb9'))) die ("Error open GZip: $filename");
	gzwrite($gf, $content);
	gzclose($gf);

}

?>

