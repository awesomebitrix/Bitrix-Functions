<?

define("DEBUG_MODE",true);

function myPrintR( $array , $file="", $line="" )
{
    if (DEBUG_MODE===true){

        echo $file.' '.$line.'<pre>';
        print_r( $array );
        echo '</pre>';

    }
}




/**
 * Генерация пароля
 * @param int $length
 * @return string
 */
function generatePassword($length = 8)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $count = mb_strlen($chars);

    for ($i = 0, $result = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= mb_substr($chars, $index, 1);
    }

    return $result;
}

/*
 * Вспомогательные функции.
 * */

/** Склонение существительных с числительными
 * @param int $n число
 * @param string $form1 Единственная форма: 1 секунда
 * @param string $form2 Двойственная форма: 2 секунды
 * @param string $form5 Множественная форма: 5 секунд
 * @return string Правильная форма
 */
function pluralForm($n, $form1, $form2, $form5) {
    $n = abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $form5;
    if ($n1 > 1 && $n1 < 5) return $form2;
    if ($n1 == 1) return $form1;
    return $form5;
}

function arrayToString($array)
{
    $sResult = "";
    foreach($array as $k=>$val){
        $val = htmlspecialchars ( $val );
        $sResult.="[".$k."=\"".$val."\"]";
        if ( isset($array[$k+1])) $sResult.="\n";
    }
    return $sResult;
}

function stringToArray($string)
{
    $arResult= array();
    $string = mb_substr($string,1,-1);
    $arStrings = explode("][",$string);

    foreach ($arStrings as $string){ //NAME="Майкл"
        preg_match( '#([^=].*)="(.*)"#', $string, $arMatches);
        if ( $arMatches[1] && $arMatches[2]){
            $arResult[ $arMatches[1] ] = htmlspecialchars_decode ($arMatches[2]);
        }
    }
    return $arResult;

}


/**
 * Размер файла в кило/мега/гига/тера/пета байтах
 * @param int $filesize — размер файла в байтах
 *
 * @return string — возвращаем размер файла в Б, КБ, МБ, ГБ или ТБ
 */
function filesize_format($filesize)
{
    $formats = array('б','Кб','Мб','Гб','Тб');// варианты размера файла
    $format = 0;// формат размера по-умолчанию

    // прогоняем цикл
    while ($filesize > 1024 && count($formats) != ++$format)
    {
        $filesize = round($filesize / 1024, 2);
    }

    // если число большое, мы выходим из цикла с
    // форматом превышающим максимальное значение
    // поэтому нужно добавить последний возможный
    // размер файла в массив еще раз
    $formats[] = 'Тб';

    return $filesize.$formats[$format];
}

//получаем код значения пользовательского свойства типа список
function getUserFieldEnumIdByCode($sXmlId)
{
    $rsField = CUserFieldEnum::GetList(array(), array("XML_ID" => $sXmlId));
    if($arEnumField = $rsField->GetNext()){
        return $arEnumField['ID'];
    }
    return false;
}


//получает айдишник значения в свойстве типа список
function getPropertyEnumIdByCode($propertyCode, $enumCode,$iIblockId)
{
    CModule::IncludeModule("iblock");

    if (strlen($enumCode)>0 && strlen($propertyCode)>0) {
        $property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"),
            Array("IBLOCK_ID"=>$iIblockId, "CODE"=>$propertyCode, 'EXTERNAL_ID'=>$enumCode));
        if($enum_fields = $property_enums->GetNext()){
            return $enum_fields["ID"];
        }
    }
    return false;

}


function getPropertyIdByCode ( $propertyCode,  $iIblockID = false, $bRefreshCache = false )
{
    if ( strlen($propertyCode)>0 ){

        global $obCache;
        $iReturnId = 0;
        $CACHE_ID = __FUNCTION__.$propertyCode.$iIblockID;
        $iCacheTime = 10800; //3 часа

        if($obCache->StartDataCache($iCacheTime, $CACHE_ID)):

            if(CModule::IncludeModule('iblock')) {
                $arFilter = Array("CODE"=>$propertyCode);
                if ($iIblockID){
                    $arFilter['IBLOCK_ID'] = $iIblockID;
                }

                $properties = CIBlockProperty::GetList(Array("id"=>"desc", "name"=>"asc"), $arFilter);
                if ($prop_fields = $properties->GetNext()){
                    $iReturnId = $prop_fields["ID"];
                }
            }

            $obCache->EndDataCache($iReturnId);
        else:
            $iReturnId = $obCache->GetVars();
        endif;

        return $iReturnId;

    }
    return false;
}


/**
 *
 * Возвращает ID раздела по его коду
 *
 * @param $sIBlockCode
 * @param bool $bRefreshCache
 * @return int
 */

function getSectionIdByCode($sSectionCode,  $bRefreshCache = false)
{
    global $obCache;
    $iReturnId = 0;
    $CACHE_ID = __FUNCTION__.$sSectionCode;
    $iCacheTime = 10800; //3 часа

    if($obCache->StartDataCache($iCacheTime, $CACHE_ID)):

        if(CModule::IncludeModule('iblock')) {
            $arFilter = Array('CODE'=>$sSectionCode);
            $db_list = CIBlockSection::GetList(Array(), $arFilter, false,array("ID"));
            if($ar_result = $db_list->GetNext()){
                $iReturnId = $ar_result['ID'];
            }
        }

        $obCache->EndDataCache($iReturnId);
    else:
        $iReturnId = $obCache->GetVars();
    endif;

    return $iReturnId;
}

/**
 *
 * Возвращает ID инфоблока по символьному коду
 *
 * @param $sIBlockCode
 * @param bool $bRefreshCache
 * @return int
 */
function getIBlockIdByCode($sIBlockCode, $bRefreshCache = false)
{
    global $obCache;
    $iReturnId = 0;
    $CACHE_ID = __FUNCTION__.$sIBlockCode.'_______';
    $iCacheTime = 10800; //3 часа

    if($obCache->StartDataCache($iCacheTime, $CACHE_ID)):

        if(CModule::IncludeModule('iblock')) {
            $arFilter = array(
                'CODE' => $sIBlockCode,
                'ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' => 'N'
            );
            $dbItems = CIBlock::GetList(array('ID' => 'ASC'), $arFilter, false);
            if($arItem = $dbItems->Fetch()) {
                $iReturnId = intval($arItem['ID']);
            }
        }

        $obCache->EndDataCache($iReturnId);
    else:
        $iReturnId = $obCache->GetVars();
    endif;

    return $iReturnId;
}

//функция добавляет новые значения в свойства типа список
function addPropertyListItem($propCode, $value,$iblock)
{

    if (is_array($value)){
        if (!$value['VALUE']) return false;
    }elseif (!$value) {
        return false;
    }

    $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("CODE"=>$propCode, "IBLOCK_ID"=>$iblock));
    if ($prop_fields = $properties->GetNext()){
        $propId = $prop_fields['ID'];
    }else {
        return false;
    }

    $propFilter = array("IBLOCK_ID" => $iblock, "PROPERTY_ID" => $propId, "VALUE" => $value);
    $arPropFields = array("PROPERTY_ID" => $propId, 'VALUE'=>$value);


    if (is_array($value) && strlen($value['EXTERNAL_ID'])>0){
        $propFilter = array("IBLOCK_ID" => $iblock, "PROPERTY_ID" => $propId, 'EXTERNAL_ID'=>$value['EXTERNAL_ID']);
        $arPropFields = array_merge(  array("PROPERTY_ID" => $propId), $value );
    }elseif (is_array($value)){
        $propFilter = array_merge (array("IBLOCK_ID" => $iblock, "PROPERTY_ID" => $propId), $value);
        $arPropFields = array_merge(  array("PROPERTY_ID" => $propId), $value );
    }

    $iPropIdStatus = 0;
    $obPropertyEnum = new CIBlockPropertyEnum;
    $rsPropertyEnums = CIBlockPropertyEnum::GetList(false, $propFilter);
    if($arPropertyEnums = $rsPropertyEnums->GetNext()){
        $obPropertyEnum->Update($arPropertyEnums['ID'], $arPropFields);
        $iPropIdStatus = $arPropertyEnums['ID'];
    }else{
        $iPropIdStatus = $obPropertyEnum->Add($arPropFields);
    }
    return $iPropIdStatus;
}


?>