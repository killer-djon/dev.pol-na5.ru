<?php
	//
	// Quick Pages application constants
	//

	// Page names
	//
	define( "PAGE_QP_QUICKPAGES", "quickpages.php" );
	define( "PAGE_QP_ADDMODPAGE", "addmodpage.php" );
	define( "PAGE_QP_PUBLISHED", "book.php" );
	define( "PAGE_QP_PUBLISHED_PRINT", "published_print.php" );
	define( "PAGE_QP_PUBLISHED_REPORT", "report_published.php" );
	define( "PAGE_QP_BOOKS", "qpbooks.php" );
	define( "PAGE_QP_ADDMODBOOK", "addmodbook.php" );
	define( "PAGE_QP_QPADDMODBOOK", "qp_am_book.php" );
	define( "PAGE_QP_ADDMODFOLDER", "addmodfolder.php" );
	define( "PAGE_QP_PAGE", "page.php" );
	define( "PAGE_QP_COPYMOVE", "copymove.php" );
	define( "PAGE_QP_ORGANIZE", "organize.php" );
	define( "PAGE_QP_PRINT", "print.php" );
	define( "PAGE_QP_GETPAGEFILE", "getpagefile.php" );
	define( "PAGE_QP_VIEW", "view.php" );
	define( "PAGE_QP_MANAGER", "quicknotesmanager.php" );
	define( "PAGE_QP_USERRIGHTS", "userrights.php" );
	define( "PAGE_QP_ACCESSRIGHTS", 'accessrightsinfo.php' );
	define( "PAGE_QP_TPLLIST", "quicknotestemplates.php" );
	define( "PAGE_QP_ADDMODTPL", "addmodtpl.php" );
	define( "PAGE_QP_ADDIMAGES", "addimages.php" );
	define( "PAGE_QP_PUBLISHSETUP", "publishsetup.php" );
	define( "PAGE_QP_PUBLISH", "publish.php" );
	define( "PAGE_QP_JUMPBOOK", "jump2book.php" );
	define( "PAGE_QP_BACKUP", "backup.php" );
	define( "PAGE_QP_GETARCHIVE", "getarchive.php" );
	define( "PAGE_QP_RESTORE", "restore.php" );

	define( "PAGE_QP_THEMES", "qpthemes.php" );
	define( "PAGE_QP_ADDNEWTHEME", "addnewtheme.php" );
	define( "PAGE_QP_ADDMODTHEME", "addmodtheme.php" );
	define( "PAGE_QP_EDITFRAME", "editframe.php" );
	define( "PAGE_QP_THMADDIMAGES", "thmaddimages.php" );

	// Records per page
	//
	define( "QP_RECORDS_PER_PAGE", 20 );

	// Attachments directory
	//
	define( "QP_ATTACHMENTS_DIR", sprintf( "%s/qp/attachments", WBS_PUBLIC_ATTACHMENTS_DIR ) );

	// Error codes
	//
	define( "QP_ERR_NOBOOK", 4000 ); // There is no desired book in the database


	$qp_book_data_schema =

	array(

// common settings

		"codepage" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>"iso-8859-1", "NAME"=>"app_iso8859_1_codepage" ),
													array( "VALUE"=>"windows-1251", "NAME"=>"app_cp1251_codepage" )
											),
									"DEFAULT" => "iso-8859-1"
								),

		"language" => array(
									"TYPE" => "text",
									"DEFAULT" => "eng",
									"LEN"=>5,
								),

		"auth" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>0, "NAME"=>"No auth" ),
													array( "VALUE"=>1, "NAME"=>"Required" )
											),
									"DEFAULT" => 0,
									"checkbox" => 1
								)


	);


	$qp_publish_data_schema =

	array(

// Plain: Top Frame

		"plain_top_visible" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>0, "NAME"=>"Hidden" ),
													array( "VALUE"=>1, "NAME"=>"Visible" )
											),
									"DEFAULT" => 1,
									"checkbox" => 1
								),

// Plain: Book Name

		"plain_bname_visible" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>0, "NAME"=>"Hidden" ),
													array( "VALUE"=>1, "NAME"=>"Visible" )
											),
									"DEFAULT" => 1,
									"checkbox" => 1
								),

		"plain_bname_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"plain_bname_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"plain_bname_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "x-large"
								),

		"plain_bname_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

		"plain_bname_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#808080",
									"text" => 1
								),

// Plain: Table Of Content header

		"plain_tochdr_visible" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>0, "NAME"=>"Hidden" ),
													array( "VALUE"=>1, "NAME"=>"Visible" )
											),
									"DEFAULT" => 1,
									"checkbox" => 1
								),

		"plain_tochdr_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"plain_tochdr_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"plain_tochdr_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "medium"
								),

		"plain_tochdr_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

		"plain_tochdr_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#C0C0C0",
									"text" => 1
								),

// Plain: Table Of Content body

		"plain_toc_visible" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>0, "NAME"=>"Hidden" ),
													array( "VALUE"=>1, "NAME"=>"Visible" )
											),
									"DEFAULT" => 1,
									"checkbox" => 1
								),

		"plain_toc_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"plain_toc_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"plain_toc_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "x-small"
								),

		"plain_toc_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#0000FF",
									"text" => 1
								),

		"plain_toc_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

// Plain: Page delimiter

		"plain_pdelim_visible" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>0, "NAME"=>"Hidden" ),
													array( "VALUE"=>1, "NAME"=>"Visible" )
											),
									"DEFAULT" => 0,
									"checkbox" => 1
								),

		"plain_pdelim_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),


// Plain: Page title

		"plain_ptitle_visible" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>0, "NAME"=>"Hidden" ),
													array( "VALUE"=>1, "NAME"=>"Visible" )
											),
									"DEFAULT" => 1,
									"checkbox" => 1
								),

		"plain_ptitle_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"plain_ptitle_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"plain_ptitle_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "medium"
								),

		"plain_ptitle_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

		"plain_ptitle_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#C0C0C0",
									"text" => 1
								),

// Plain: Body defaults

		"plain_body_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"plain_body_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"plain_body_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "x-small"
								),

		"plain_body_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#000000",
									"text" => 1
								),

		"plain_body_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

// Plain: Links

		"plain_link_fg" => array(
									"TYPE" => "text",
									"DEFAULT"=> "#0000FF",
									"text" => 1
								),

		"plain_link_bg" => array(
									"TYPE" => "text",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

// Plain: Body defaults

		"plain_toclink_visible" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>0, "NAME"=>"Hidden" ),
													array( "VALUE"=>1, "NAME"=>"Visible" )
											),
									"DEFAULT" => 1,
									"checkbox" => 1
								),

		"plain_toclink_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"plain_toclink_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"plain_toclink_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "x-small"
								),

		"plain_toclink_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#0000FF",
									"text" => 1
								),

		"plain_toclink_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

		"plain_toclink_adjust" => array(
									"TYPE" => "text",
									"DEFAULT"=> "center"
								),

// Plain: H1

		"plain_h1_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"plain_h1_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"plain_h1_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "x-large"
								),

		"plain_h1_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#000000",
									"text" => 1
								),

		"plain_h1_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

// Plain: H2

		"plain_h2_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"plain_h2_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"plain_h2_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "large"
								),

		"plain_h2_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#000000",
									"text" => 1
								),

		"plain_h2_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

// Plain: H3

		"plain_h3_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"plain_h3_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"plain_h3_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "medium"
								),

		"plain_h3_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#000000",
									"text" => 1
								),

		"plain_h3_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),


// Tree: Top Frame

		"tree_top_visible" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>0, "NAME"=>"Hidden" ),
													array( "VALUE"=>1, "NAME"=>"Visible" )
											),
									"DEFAULT" => 1,
									"checkbox" => 1
								),

// Tree: Book Name

		"tree_bname_visible" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>0, "NAME"=>"Hidden" ),
													array( "VALUE"=>1, "NAME"=>"Visible" )
											),
									"DEFAULT" => 1,
									"checkbox" => 1
								),

		"tree_bname_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"tree_bname_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"tree_bname_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "xx-large"
								),

		"tree_bname_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

		"tree_bname_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#A9A9A9",
									"text" => 1
								),

// Tree: Search box panel

		"tree_srch_visible" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>0, "NAME"=>"Hidden" ),
													array( "VALUE"=>1, "NAME"=>"Visible" )
											),
									"DEFAULT" => 1,
									"checkbox" => 1
								),

		"tree_srch_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"tree_srch_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"tree_srch_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "x-small"
								),

		"tree_srch_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#000000",
									"text" => 1
								),

		"tree_srch_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#D3D3D3",
									"text" => 1
								),

// Tree: TOC header

		"tree_tochdr_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"tree_tochdr_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "bold"
								),

		"tree_tochdr_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "x-small"
								),

		"tree_tochdr_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#000000",
									"text" => 1
								),

		"tree_tochdr_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#D3D3D3",
									"text" => 1
								),

// Tree: TOC body

		"tree_toc_wrap" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
														array( "VALUE"=>0, "NAME"=>"Yes" ),
														array( "VALUE"=>1, "NAME"=>"No" )
													),
									"DEFAULT" => 0,
									"checkbox" => 1,
									"notuse" => 1
								),

		"tree_toc_pageicons" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
												array( "VALUE"=>0, "NAME"=>"Yes" ),
												array( "VALUE"=>1, "NAME"=>"No" )
											),
									"DEFAULT" => 1,
									"checkbox" => 1
								),

		"tree_toc_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"tree_toc_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"tree_toc_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "x-small"
								),

		"tree_toc_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#000000",
									"text" => 1
								),

		"tree_toc_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

// Tree: TOC selected row

		"tree_tocs_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

		"tree_tocs_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#0000FF",
									"text" => 1
								),

// Tree: Splitter

		"tree_splitter_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#D3D3D3",
									"text" => 1
								),

// Tree: Button

		"tree_btn_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"tree_btn_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"tree_btn_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "x-small"
								),

		"tree_btn_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#000000",
									"text" => 1
								),

		"tree_btn_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

// Tree: Body

		"tree_body_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50
								),

		"tree_body_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"tree_body_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "x-small"
								),

		"tree_body_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#000000",
									"text" => 1
								),

		"tree_body_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

// Tree: Prev|Next link

		"tree_pn_visible" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>0, "NAME"=>"Hidden" ),
													array( "VALUE"=>1, "NAME"=>"Visible" )
											),
									"DEFAULT" => 1,
									"checkbox" => 1
								),

		"tree_pn_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"tree_pn_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"tree_pn_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "x-small"
								),

		"tree_pn_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#0000FF",
									"text" => 1
								),

		"tree_pn_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

		"tree_pn_pos" => array(
									"TYPE" => "text",
									"DEFAULT"=> "both"
								),

		"tree_pn_adjust" => array(
									"TYPE" => "text",
									"DEFAULT"=> "left"
								),


// Tree: Links

		"tree_link_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#0000FF",
									"text" => 1
								),

		"tree_link_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

// Tree: H1

		"tree_h1_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"tree_h1_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"tree_h1_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "large"
								),

		"tree_h1_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#000000",
									"text" => 1
								),

		"tree_h1_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

// Tree: H2

		"tree_h2_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"tree_h2_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"tree_h2_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "medium"
								),

		"tree_h2_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#000000",
									"text" => 1
								),

		"tree_h2_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								),

// Tree: H3

		"tree_h3_ff" => array(
									"TYPE" => "text",
									"DEFAULT"=> "Verdana, Helvetica, sans-serif",
									"LEN"=>50,
								),

		"tree_h3_fs" => array(
									"TYPE" => "text",
									"DEFAULT"=> "normal"
								),

		"tree_h3_fsz" => array(
									"TYPE" => "text",
									"DEFAULT"=> "medium"
								),

		"tree_h3_fg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#000000",
									"text" => 1
								),

		"tree_h3_bg" => array(
									"TYPE" => "color",
									"DEFAULT"=> "#FFFFFF",
									"text" => 1
								)

);




$qp_colors = array (

	"#000000"=>"black",
	"#000080"=>"navy",
	"#00008B"=>"darkblue",
	"#0000CD"=>"mediumblue",
	"#0000FF"=>"blue",
	"#006400"=>"darkgreen",
	"#008000"=>"green",
	"#008080"=>"teal",
	"#008B8B"=>"darkcyan",
	"#00BFBF"=>"deepskyblue",
	"#00CED1"=>"darkturquoise",
	"#00FA9A"=>"mediumspringgreen",
	"#00FF00"=>"lime",
	"#00FF7F"=>"springgreen",
	"#00FFFF"=>"aqua",
	"#00FFFF"=>"cyan",
	"#191970"=>"midnightblue",
	"#1E90FF"=>"dodgerblue",
	"#20B2AA"=>"lightseagreen",
	"#228B22"=>"forestgreen",
	"#2E8B57"=>"seagreen",
	"#2F4F4F"=>"darkslategray",
	"#32CD32"=>"limegreen",
	"#3CB371"=>"mediumseagreen",
	"#40E0D0"=>"turquoise",
	"#4169E1"=>"royalblue",
	"#4682B4"=>"steelblue",
	"#483D8B"=>"darkslateblue",
	"#48D1CC"=>"mediumturquoise",
	"#4B0082"=>"indigo",
	"#556B2F"=>"darkolivegreen",
	"#5F9EA0"=>"cadetblue",
	"#6495ED"=>"cornflowerblue",
	"#66CDAA"=>"mediumaquamarine",
	"#696969"=>"dimgray",
	"#6A5ACD"=>"slateblue",
	"#6B8E23"=>"olivedrab",
	"#708090"=>"slategray",
	"#778899"=>"lightslategrey",
	"#7B68EE"=>"mediumslateblue",
	"#7CFC00"=>"lawngreen",
	"#7FFF00"=>"chartreuse",
	"#7FFFD4"=>"aquamarine",
	"#800000"=>"maroon",
	"#800080"=>"purple",
	"#808000"=>"olive",
	"#808080"=>"gray",
	"#87CEEB"=>"skyblue",
	"#87CEFA"=>"lightskyblue",
	"#8A2BE2"=>"blueviolet",
	"#8B0000"=>"darkred",
	"#8B008B"=>"darkmagenta",
	"#8B4513"=>"saddlebrown",
	"#8FBC8F"=>"darkseagreen",
	"#90EE90"=>"lightgreen",
	"#9370DB"=>"mediumpurple",
	"#9400D3"=>"darkviolet",
	"#98FB98"=>"palegreen",
	"#9932CC"=>"darkorchid",
	"#9ACD32"=>"yellowgreen ",
	"#A0522D"=>"sienna",
	"#A52A2A"=>"brown",
	"#A9A9A9"=>"darkgray",
	"#ADD8E6"=>"lightblue",
	"#ADFF2F"=>"greenyellow",
	"#AFEEEE"=>"paleturquoise",
	"#B0C4DE"=>"lightsteelblue",
	"#B0E0E6"=>"powderblue",
	"#B22222"=>"firebrick",
	"#B8860B"=>"darkgoldenrod",
	"#BA55D3"=>"mediumorchid",
	"#BC8F8F"=>"rosybrown",
	"#BDB76B"=>"darkkhaki",
	"#C0C0C0"=>"silver",
	"#C71585"=>"mediumvioletred",
	"#CD5C5C"=>"indianred",
	"#CD853F"=>"peru",
	"#D2691E"=>"chocolate",
	"#D2B48C"=>"tan",
	"#D3D3D3"=>"lightgrey",
	"#D8BFD8"=>"thistle",
	"#DA70D6"=>"orchid",
	"#DAA520"=>"goldenrod",
	"#DB7093"=>"palevioletred",
	"#DC143C"=>"crimson",
	"#DCDCDC"=>"gainsboro",
	"#DDA0DD"=>"plum",
	"#DEB887"=>"burlywood",
	"#E0FFFF"=>"lightcyan",
	"#E6E6FA"=>"lavender",
	"#E9967A"=>"darksalmon",
	"#EE82EE"=>"violet",
	"#EEE8AA"=>"palegoldenrod",
	"#F08080"=>"lightcoral",
	"#F0E68C"=>"khaki",
	"#F0F8FF"=>"aliceblue",
	"#F0FFF0"=>"honeydew",
	"#F0FFFF"=>"azure",
	"#F4A460"=>"sandybrown",
	"#F5DEB3"=>"wheat",
	"#F5F5DC"=>"beige",
	"#F5F5F5"=>"whitesmoke",
	"#F5FFFA"=>"mintcream",
	"#F8F8FF"=>"ghostwhite",
	"#FA8072"=>"salmon",
	"#FAEBD7"=>"antiquewhite",
	"#FAF0E6"=>"linen",
	"#FAFAD2"=>"lightgoldenrodyellow",
	"#FDF5E6"=>"oldlace",
	"#FF0000"=>"red",
	"#FF00FF"=>"fuchsia",
	"#FF00FF"=>"magenta",
	"#FF1493"=>"deeppink",
	"#FF4500"=>"orangered",
	"#FF6347"=>"tomato",
	"#FF69B4"=>"hotpink",
	"#FF7F50"=>"coral",
	"#FF8C00"=>"darkorange",
	"#FFA07A"=>"lightsalmon",
	"#FFA500"=>"orange",
	"#FFB6C1"=>"lightpink",
	"#FFC0CB"=>"pink",
	"#FFD700"=>"gold",
	"#FFDAB9"=>"peachpuff",
	"#FFDEAD"=>"navajowhite",
	"#FFE4B5"=>"moccasin",
	"#FFE4C4"=>"bisque",
	"#FFE4E1"=>"mistyrose",
	"#FFEBCD"=>"blanchedalmond",
	"#FFEFD5"=>"papayawhip",
	"#FFF0F5"=>"lavenderblush",
	"#FFF5EE"=>"seashell",
	"#FFF8DC"=>"cornsilk",
	"#FFFACD"=>"lemonchiffon",
	"#FFFAF0"=>"floralwhite",
	"#FFFAFA"=>"snow",
	"#FFFF00"=>"yellow",
	"#FFFFE0"=>"lightyellow",
	"#FFFFF0"=>"ivory",
	"#FFFFFF"=>"white color"

);

$qp_optFWeight = array ( 'normal','bold','bolder','lighter' );

$qp_optFFamily = array ( 'Arial, Helvetica, sans-serif', 'Georgia, Palatino, Times New Roman, serif','Times New Roman,serif','Geneva, Verdana, Helvetica, san-serif.','Verdana, Helvetica, sans-serif','Courier New, Courier, mono' );

$qp_optFStyle = array ( 'normal','italic','oblique' );

$qp_optFSize = array ( '8pt', '9pt', '10pt', '11pt', '12pt', '13pt', '14pt', '15pt', '16pt', '17pt', '18pt', 'larger','smaller','xx-large','x-large','large','medium','small','x-small','xx-small' );

$qp_optAdjust = array ( 'left','center','right' );

$qp_optPosition = array ( 'top'=>'qpt_top_position_label', 'bottom'=>'qpt_bottom_position_label', 'both'=>'qpt_topbottom_position_label' );

?>