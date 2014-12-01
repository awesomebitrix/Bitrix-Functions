<?
/*
 *
 * Скрипт автоматически кэширует все картинки в инфоблоке для ускорения загрузки страниц каталога
 *
 * */
class ImagesCronResize {
    const MAX_EXECUTION_TIME = 40;
    const AGENT_TIME_INTERVAL = 5; //минут

    /*
     * Агент ресайзит все превьюшки в инфоблоке под нужные размеры
     */
    public static function resizeAgent($iLastId=0)
    {
        $startAgentTimestamp = time();

        CModule::IncludeModule("iblock");
        $arSelect = Array("ID", "NAME", "PREVIEW_PICTURE");
        $arFilter = Array("IBLOCK_ID" => CATALOG_IBLOCK_ID, "ACTIVE" => "Y",">ID" => $iLastId );
        $res = CIBlockElement::GetList(Array("ID"=>"ASC"), $arFilter, false, false, $arSelect);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();

            if ($arFields['PREVIEW_PICTURE']>0) {


                CFile::ResizeImageGet( $arFields['PREVIEW_PICTURE'], array('width'=>SMALL_PREVIEW_WIDTH, 'height'=>SMALL_PREVIEW_HEIGHT), BX_RESIZE_IMAGE_PROPORTIONAL, true); //BX_RESIZE_IMAGE_EXACT
                CFile::ResizeImageGet( $arFields['PREVIEW_PICTURE'], array('width'=>BIG_PREVIEW_WIDTH, 'height'=>BIG_PREVIEW_HEIGHT), BX_RESIZE_IMAGE_PROPORTIONAL, true);

            }

            if ((time()-$startAgentTimestamp)>self::MAX_EXECUTION_TIME){

                //Добавляем новый агент через 5 минут
                self::addOneMoreStepAgent($arFields['ID']);

                return false;
                break;
            }

        }
        return get_called_class()."::resizeAgent();";
    }

    public static function addAgent()
    {
        CAgent::AddAgent(
            get_called_class()."::resizeAgent();", // имя функции
            "",                          // идентификатор модуля
            "N",                                  // агент не критичен к кол-ву запусков
            86400,                                // интервал запуска - 1 сутки
            date("d.m.Y 05:00:00",strtotime("+1 day")),// дата первой проверки на запуск
            "Y",                                  // агент активен
            date("d.m.Y 05:00:00",strtotime("+1 day")),// дата первого запуска
            30);

    }

    protected  function addOneMoreStepAgent($id)
    {
        CAgent::AddAgent(
            get_called_class()."::resizeAgent(".intval($id).");", // имя функции
            "",                          // идентификатор модуля
            "N",                                  // агент не критичен к кол-ву запусков
            86400,                                // интервал запуска - 1 сутки
            date("d.m.Y H:i:s",strtotime("+".self::AGENT_TIME_INTERVAL." minute")),// дата первой проверки на запуск
            "Y",                                  // агент активен
            date("d.m.Y H:i:s",strtotime("+".self::AGENT_TIME_INTERVAL." minute")),// дата первого запуска
            30);

    }

}