<?
require("ImagesCronResize.php");

define("CATALOG_IBLOCK_ID",47); //ид инфоблока

//Размеры картинок
define("SMALL_PREVIEW_WIDTH",112);
define("SMALL_PREVIEW_HEIGHT",75);
define("BIG_PREVIEW_WIDTH",152);
define("BIG_PREVIEW_HEIGHT",139);

//добавление агента
$obRes = CAgent::GetList($arOrder = Array("ID" => "DESC", array("NAME"=>"%ImagesCronResize%");
if(!$obRes->Fetch()){
    ImagesCronResize::addAgent();
}