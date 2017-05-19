<?php
/**
 * @desc 汉字转拼音 (utf8)
 */

include 'Dict.class.php';

class Pinyin_Pinyin
{
    /**
     * @desc split string
     * @param string $string
     * @return array
     **/
    private function splitString($string)
    {
        $arrResult = array();

        $intLen = mb_strlen($string);
        while ($intLen) {
            $arrResult[] = mb_substr($string, 0, 1, 'utf8');
            $string = mb_substr($string, 1, $intLen, 'utf8');
            $intLen = mb_strlen($string);
        }

        return $arrResult;
    }

    /**
     * @desc change to single character list to pinyin list
     * @param array $arrStringList
     * @return array
     **/
    private function toPinyinList($arrStringList)
    {
        $arrResult = array();

        if (!is_array($arrStringList)) {
            return $arrResult;
        }
        foreach ($arrStringList as $string) {
            switch (strlen($string)) {
                case 1:
                    $arrResult[] = array($string);
                    break;
                case 3:
                    if (isset(Pinyin_ChinesePinyinTable::$arrChinesePinyinTable[$string])) {
                        $arrResult[] =
                            array_unique(Pinyin_ChinesePinyinTable::$arrChinesePinyinTable[$string]);
                    } else {
                        $arrResult[] = array($string);
                    }
                    break;
                default :
                    $arrResult[] = array($string);
            }
        }
        return $arrResult;
    }

    /**
     * @desc convert chinese(include letter & number) to pinyin
     * @param string $string
     * @param boolean $isSimple
     * @param boolean $isInitial
     * @param boolean $isPolyphone
     * @param boolean $isAll
     * @return mixed
     **/
    public static function ChineseToPinyin($string, $isSimple = true, $isInitial = false,
                                           $isPolyphone = false, $isAll = false)
    {

        $result = '';

        if (empty($string)) {
            return $result;
        }
        $arrStringList = self::splitString($string);
        if (!is_array($arrStringList)) {
            return $result;
        }

        $arrPinyinList = self::toPinyinList($arrStringList);
        if (!is_array($arrPinyinList)) {
            return $result;
        }

        if ($isSimple === true) {
            foreach ($arrPinyinList as $arrPinyin) {
                if (empty($arrPinyin)) {
                    continue;
                }
                $result .= $arrPinyin[0];
            }

            return $result;
        }

        if (count($arrPinyinList) > 1) {
            $arrFirstPinyin = array_shift($arrPinyinList);
            if (($isInitial !== true) || ($isAll === true)) {
                $arrPrevPinyin = $arrFirstPinyin;
                foreach ($arrPinyinList as $arrPinyin) {
                    $arrFullPinyin = array();
                    foreach ($arrPrevPinyin as $strPrevPinyin) {
                        foreach ($arrPinyin as $strPinyin) {
                            $arrFullPinyin[] = $strPrevPinyin . $strPinyin;
                        }
                    }
                    $arrPrevPinyin = $arrFullPinyin;
                }
            }
            if (($isInitial === true) || ($isAll === true)) {


                foreach ($arrFirstPinyin as $k => $v) {
                    if (ord($v) > 129) {
                        $arrPrevInitialPinyin[$k] = $v;
                    } else {
                        $arrPrevInitialPinyin[$k] = substr($v, 0, 1);
                    }
                }
                unset($v);

                foreach ($arrPinyinList as $arrPinyin) {
                    $arrInitialPinyin = array();
                    foreach ($arrPrevInitialPinyin as $strPrevPinyin) {
                        foreach ($arrPinyin as $strPinyin) {
                            if (ord($strPinyin) > 129) {
                                $arrInitialPinyin[] = $strPrevPinyin . $strPinyin;
                            } else {
                                $arrInitialPinyin[] = $strPrevPinyin . substr($strPinyin, 0, 1);
                            }
                        }
                    }
                    $arrPrevInitialPinyin = $arrInitialPinyin;
                }
            }
            if ($isAll === true) {
                $result['full'] = $arrFullPinyin;
                $result['initial'] = $arrInitialPinyin;
            } elseif ($isPolyphone === true) {
                if (($isInitial === true)) {
                    $result = $arrInitialPinyin;
                } else {
                    $result = $arrFullPinyin;
                }
            } else {
                if (($isInitial === true)) {
                    $result = reset($arrInitialPinyin);
                } else {
                    $result = reset($arrFullPinyin);
                }
            }
        } else {
            $arrFirstPinyin = array_shift($arrPinyinList);
            $result = array();
            foreach ($arrFirstPinyin as $arrPinyin) {
                $result[] = substr($arrPinyin, 0, 1);
            }
        }
        $arr = array_unique($result);
        foreach ($arr as &$v) {
            $v = strtolower($v);
            $v = preg_replace("/[^a-z0-9]+/", '', $v);
            unset($v);
        }
        unset($v);
        $arr = array_unique($arr);
        return implode(',', $arr);
    }
}
