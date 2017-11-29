<?php
/**
 * Created by PhpStorm.
 * User: zharikov
 * Date: 27.11.2017
 * Time: 18:56
 */

use DEX\IParser;
use Zend\Db\Adapter\Adapter;
use Zend\Log\Logger;
use Zend\Config\Config;

class WeatherImpl implements IParser
{
    /**
     * @var Adapter
     */
    protected $adapter_db;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var integer
     */
    protected $cityId;

    /**
     * @var stdClass
     */
    protected $decode;

    /**
     * WeatherImpl constructor.
     * @param $cityId integer
     * @param $logger Logger
     */
    public function __construct($cityId, $logger, $adapter_db)
    {
        $this->cityId = $cityId;
        $this->logger = $logger;
        $this->adapter_db = $adapter_db;
    }

    /**
     * @param Config $config
     * @return bool
     */
    public function before($config)
    {
        $this->logger->info("Connect to DB");

        $decode = null;
        $this->decode = $this->adapter_db->query(
            "SELECT param_name, param_value, decode_name FROM public.decode_params WHERE lang = 'ru'",
            Adapter::QUERY_MODE_EXECUTE);

        return true;
    }

    public function validation($body)
    {
        return $body !== "";
    }

    public function run($body)
    {
        $this->logger->info("PARSE: run");

        libxml_use_internal_errors(true);

        $forecast = array();

        $dom = new DomDocument();
        $dom->loadHTML($body);

        $xpath = new DOMXpath($dom);
        $water_t = "";
        $is_felt_t = "";

        $waterTemp = $xpath->query("//div[@class='wicon water']/dd[@class='value m_temp c']");

        if (is_null($waterTemp)) {
            throw new Exception("cityId:$this->cityId - parse error #1");
        }

        $water_t = $waterTemp->item(0)->childNodes->item(0)->nodeValue;

        $elements = $xpath->query("//table[thead and tbody]");

        if (is_null($elements)) {
            throw new Exception("cityId:$this->cityId - parse error #2");
        }

        $day = 0;
        foreach ($elements as $table) {
            $hours = array(
                0 => 2,
                1 => 8,
                2 => 12,
                3 => 18
            );

            $cloudAndPrecip = $xpath->query("tbody/tr/td[2]", $table);
            $temp = $xpath->query("tbody/tr/td[3]/span[1]", $table);
            $pressure = $xpath->query("tbody/tr/td[4]/span[1]", $table);
            $wind_speed = $xpath->query("tbody/tr/td[5]/dl[1]/dd[1]/span[1]", $table);
            $wind_direction = $xpath->query("tbody/tr/td[5]/dl[1]/dt[1]", $table);
            $humidity = $xpath->query("tbody/tr/td[6]", $table);
            $is_felt_t = $xpath->query("tbody/tr/td[7]/span[1]", $table);

            foreach ($hours as $n => $h) {
                $fc = array();
                $fc['waterTmp'] = $this->_replaceBadchars($water_t);

                $time = mktime($h, 0, 0, date('m'), date('d') + $day, date('Y'));

                $cap = $cloudAndPrecip->item($n)->nodeValue;

                if ($cap == "") {
                    $this->logger->err("PARSE: warning cloudAndPrecip eq empty, time: " . $time);
                    continue;
                }

                $cloudAndPrecipArr = explode(", ", $cap);

                $cloudDP = $this->_findDP($this->decode, $cloudAndPrecipArr[0], "cloud");
                if (is_null($cloudDP)) {
                    $this->logger->err("CRON 2 PARSE: warning cloudDP eq null, time: " . $time);
                    continue;
                }

                $fc['cloud'] = $cloudDP;
                $fc['precip'] = isset($cloudAndPrecipArr[1]) ? $this->_findDP($this->decode,
                    $cloudAndPrecipArr[1],
                    "precip") : 200;
                $fc['temp'] = $this->_replaceBadchars($temp->item($n)->nodeValue);
                $fc['pressure'] = $pressure->item($n)->nodeValue;
                $fc['humidity'] = $humidity->item($n)->nodeValue;
                $fc['windSpeed'] = $wind_speed->item($n)->nodeValue;
                $fc['windDirection'] = $this->_findDPwD($wind_direction->item($n)->nodeValue);
                $fc['isFeltTmp'] = $this->_replaceBadchars($is_felt_t->item($n)->nodeValue);

                if ($fc['waterTmp'] == "-") {
                    $fc['waterTmp'] = $fc['isFeltTmp'];
                }

                $fc['cityId'] = $this->cityId;

                $forecast[date("Y-m-d H:i:s", $time)] = $fc;
            }

            $day++;
        }

        return $forecast;
    }

    public function after()
    {
        // TODO: Implement after() method.
    }

    private function _replaceBadchars($str)
    {
        $badchars = array(
            "/в€’/u" => '-',
            "/&minus;/" => '-',
            "/−/" => '-'
        );
        $pattern = array_keys($badchars);
        $replacement = array_values($badchars);

        return preg_replace($pattern, $replacement, $str);
    }

    private function _strToLower($str)
    {
        $pattern = array(
            "'А'",
            "'Б'",
            "'В'",
            "'Г'",
            "'Д'",
            "'Е'",
            "'Ё'",
            "'Ж'",
            "'З'",
            "'И'",
            "'Й'",
            "'К'",
            "'Л'",
            "'М'",
            "'Н'",
            "'О'",
            "'П'",
            "'Р'",
            "'С'",
            "'Т'",
            "'У'",
            "'Ф'",
            "'Х'",
            "'Ц'",
            "'Ч'",
            "'Ш'",
            "'Щ'",
            "'Ъ'",
            "'Ы'",
            "'Ь'",
            "'Э'",
            "'Ю'",
            "'Я'",
            "'A'",
            "'B'",
            "'C'",
            "'D'",
            "'E'",
            "'F'",
            "'G'",
            "'H'",
            "'I'",
            "'J'",
            "'K'",
            "'L'",
            "'M'",
            "'N'",
            "'O'",
            "'P'",
            "'Q'",
            "'R'",
            "'S'",
            "'T'",
            "'U'",
            "'V'",
            "'W'",
            "'X'",
            "'Y'",
            "'Z'"
        );

        $replacement = array(
            "а",
            "б",
            "в",
            "г",
            "д",
            "е",
            "ё",
            "ж",
            "з",
            "и",
            "й",
            "к",
            "л",
            "м",
            "н",
            "о",
            "п",
            "р",
            "с",
            "т",
            "у",
            "ф",
            "х",
            "ц",
            "ч",
            "ш",
            "щ",
            "ъ",
            "ы",
            "ь",
            "э",
            "ю",
            "я",
            "a",
            "b",
            "c",
            "d",
            "e",
            "f",
            "g",
            "h",
            "i",
            "j",
            "k",
            "l",
            "m",
            "n",
            "o",
            "p",
            "q",
            "r",
            "s",
            "t",
            "u",
            "v",
            "w",
            "x",
            "y",
            "z"
        );

        return preg_replace($pattern, $replacement, $str);
    }

    private function _findDPwD($decode)
    {
        $windDirection = array(
            "" => 0, // error
            "Ш" => 300, // штиль
            "С" => 301, // северный
            "СВ" => 302, // северо-восточный
            "В" => 303, // восточный
            "ЮВ" => 304, // юго-вочтоный
            "Ю" => 305, // южный
            "ЮЗ" => 306, // юго-западный
            "З" => 307, // западный
            "СЗ" => 308 // северо-западный
        );

        return $windDirection[$decode];
    }

    private function _findDP($decodeParamsResult, $decode, $type)
    {
        foreach ($decodeParamsResult as $dp) {
            if ($dp->param_name == $type) {
                if ($dp->decode_name == trim($this->_strToLower($decode))) {
                    return $dp->param_value;
                }
            }
        }

        return null;
    }
}