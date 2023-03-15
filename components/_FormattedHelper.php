<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */
namespace app\components;

use app\models\UserAccount;
use yii\base\Component;

class _FormattedHelper extends Component
{
    const CURRENCY_NUMERIC_FORMAT = 'Currency';
    const DECIMAL_NUMERIC_FORMAT = 'Decimal';

    const TEXT_FORMAT = 'None';
    const EMAIL_TEXT_FORMAT = 'Email';
    const DATE_TEXT_FORMAT = 'Date';
    const DATE_TIME_TEXT_FORMAT = 'Date/Time';
	const PHONE_TEXT_FORMAT = 'Phone';

    public $timeZone;
    public $dateFormat;
    public $timeFormat;
    public $currencyFormat;
    public $currency;

    public static $currencyMap = [
        'AED' => '&#1583;.&#1573;', // ?
        'AFN' => '&#65;&#102;',
        'ALL' => '&#76;&#101;&#107;',
        'AMD' => '',
        'ANG' => '&#402;',
        'AOA' => '&#75;&#122;', // ?
        'ARS' => '&#36;',
        'AUD' => '&#36;',
        'AWG' => '&#402;',
        'AZN' => '&#1084;&#1072;&#1085;',
        'BAM' => '&#75;&#77;',
        'BBD' => '&#36;',
        'BDT' => '&#2547;', // ?
        'BGN' => '&#1083;&#1074;',
        'BHD' => '.&#1583;.&#1576;', // ?
        'BIF' => '&#70;&#66;&#117;', // ?
        'BMD' => '&#36;',
        'BND' => '&#36;',
        'BOB' => '&#36;&#98;',
        'BRL' => '&#82;&#36;',
        'BSD' => '&#36;',
        'BTN' => '&#78;&#117;&#46;', // ?
        'BWP' => '&#80;',
        'BYR' => '&#112;&#46;',
        'BZD' => '&#66;&#90;&#36;',
        'CAD' => '&#36;',
        'CDF' => '&#70;&#67;',
        'CHF' => '&#67;&#72;&#70;',
        'CLF' => '', // ?
        'CLP' => '&#36;',
        'CNY' => '&#165;',
        'COP' => '&#36;',
        'CRC' => '&#8353;',
        'CUP' => '&#8396;',
        'CVE' => '&#36;', // ?
        'CZK' => '&#75;&#269;',
        'DJF' => '&#70;&#100;&#106;', // ?
        'DKK' => '&#107;&#114;',
        'DOP' => '&#82;&#68;&#36;',
        'DZD' => '&#1583;&#1580;', // ?
        'EGP' => '&#163;',
        'ETB' => '&#66;&#114;',
        'EUR' => '&#8364;',
        'FJD' => '&#36;',
        'FKP' => '&#163;',
        'GBP' => '&#163;',
        'GEL' => '&#4314;', // ?
        'GHS' => '&#162;',
        'GIP' => '&#163;',
        'GMD' => '&#68;', // ?
        'GNF' => '&#70;&#71;', // ?
        'GTQ' => '&#81;',
        'GYD' => '&#36;',
        'HKD' => '&#36;',
        'HNL' => '&#76;',
        'HRK' => '&#107;&#110;',
        'HTG' => '&#71;', // ?
        'HUF' => '&#70;&#116;',
        'IDR' => '&#82;&#112;',
        'ILS' => '&#8362;',
        'INR' => '&#8377;',
        'IQD' => '&#1593;.&#1583;', // ?
        'IRR' => '&#65020;',
        'ISK' => '&#107;&#114;',
        'JEP' => '&#163;',
        'JMD' => '&#74;&#36;',
        'JOD' => '&#74;&#68;', // ?
        'JPY' => '&#165;',
        'KES' => '&#75;&#83;&#104;', // ?
        'KGS' => '&#1083;&#1074;',
        'KHR' => '&#6107;',
        'KMF' => '&#67;&#70;', // ?
        'KPW' => '&#8361;',
        'KRW' => '&#8361;',
        'KWD' => '&#1583;.&#1603;', // ?
        'KYD' => '&#36;',
        'KZT' => '&#1083;&#1074;',
        'LAK' => '&#8365;',
        'LBP' => '&#163;',
        'LKR' => '&#8360;',
        'LRD' => '&#36;',
        'LSL' => '&#76;', // ?
        'LTL' => '&#76;&#116;',
        'LVL' => '&#76;&#115;',
        'LYD' => '&#1604;.&#1583;', // ?
        'MAD' => '&#1583;.&#1605;.', //?
        'MDL' => '&#76;',
        'MGA' => '&#65;&#114;', // ?
        'MKD' => '&#1076;&#1077;&#1085;',
        'MMK' => '&#75;',
        'MNT' => '&#8366;',
        'MOP' => '&#77;&#79;&#80;&#36;', // ?
        'MRO' => '&#85;&#77;', // ?
        'MUR' => '&#8360;', // ?
        'MVR' => '.&#1923;', // ?
        'MWK' => '&#77;&#75;',
        'MXN' => '&#36;',
        'MYR' => '&#82;&#77;',
        'MZN' => '&#77;&#84;',
        'NAD' => '&#36;',
        'NGN' => '&#8358;',
        'NIO' => '&#67;&#36;',
        'NOK' => '&#107;&#114;',
        'NPR' => '&#8360;',
        'NZD' => '&#36;',
        'OMR' => '&#65020;',
        'PAB' => '&#66;&#47;&#46;',
        'PEN' => '&#83;&#47;&#46;',
        'PGK' => '&#75;', // ?
        'PHP' => '&#8369;',
        'PKR' => '&#8360;',
        'PLN' => '&#122;&#322;',
        'PYG' => '&#71;&#115;',
        'QAR' => '&#65020;',
        'RON' => '&#108;&#101;&#105;',
        'RSD' => '&#1044;&#1080;&#1085;&#46;',
        'RUB' => '&#8381;',
        'RWF' => '&#1585;.&#1587;',
        'SAR' => '&#65020;',
        'SBD' => '&#36;',
        'SCR' => '&#8360;',
        'SDG' => '&#163;', // ?
        'SEK' => '&#107;&#114;',
        'SGD' => '&#36;',
        'SHP' => '&#163;',
        'SLL' => '&#76;&#101;', // ?
        'SOS' => '&#83;',
        'SRD' => '&#36;',
        'STD' => '&#68;&#98;', // ?
        'SVC' => '&#36;',
        'SYP' => '&#163;',
        'SZL' => '&#76;', // ?
        'THB' => '&#3647;',
        'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
        'TMT' => '&#109;',
        'TND' => '&#1583;.&#1578;',
        'TOP' => '&#84;&#36;',
        'TRY' => '&#8356;', // New Turkey Lira (old symbol used)
        'TTD' => '&#36;',
        'TWD' => '&#78;&#84;&#36;',
        'TZS' => '',
        'UAH' => '&#8372;',
        'UGX' => '&#85;&#83;&#104;',
        'USD' => '&#36;',
        'UYU' => '&#36;&#85;',
        'UZS' => '&#1083;&#1074;',
        'VEF' => '&#66;&#115;',
        'VND' => '&#8363;',
        'VUV' => '&#86;&#84;',
        'WST' => '&#87;&#83;&#36;',
        'XAF' => '&#70;&#67;&#70;&#65;',
        'XCD' => '&#36;',
        'XDR' => '',
        'XOF' => '',
        'XPF' => '&#70;',
        'YER' => '&#65020;',
        'ZAR' => '&#82;',
        'ZMK' => '&#90;&#75;', // ?
        'ZWL' => '&#90;&#36;',
    ];

    private $revertFormat;

    public function __construct(array $config = [])
    {
        $settings = UserAccount::getSettings();

        if (!empty($settings->timezone_code)) {
            $this->timeZone = $settings->timezone_code;
        }

        if (!empty($settings->dateformat_code) && !empty($settings->timeformat_code)) {
            $this->dateFormat = $settings->dateformat_code;
            $this->timeFormat = $settings->timeformat_code;
        }

        if (!empty($settings->currencyformat_code)) {
            $this->currencyFormat = $settings->currencyformat_code;
            $this->currency = $settings->currencytype_code;
        }

        parent::__construct($config);
    }

    public function run($param, $format, $additionalParam = null)
    {
        switch ($format) {
            case self::DATE_TEXT_FORMAT:
                return $this->date($param);
                break;
            case self::DATE_TIME_TEXT_FORMAT:
                return $this->dateTime($param);
                break;
            case self::CURRENCY_NUMERIC_FORMAT:
                return $this->currency($param);
                break;
            case self::DECIMAL_NUMERIC_FORMAT:
                return $this->decimal($param, $additionalParam);
                break;
			case self::PHONE_TEXT_FORMAT:
                return $this->phone($param);
                break;
            default:
                return $param;
        }
    }

    public static function getCurrencySuffix() {
        $settings = UserAccount::getSettings();
        return (!empty(self::$currencyMap[$settings->currencytype_code])) ? html_entity_decode(self::$currencyMap[$settings->currencytype_code]) : $settings->currencytype_code;
    }

    public static function getDefaultDateFormat() {
        if ($settings = UserAccount::getSettings()) {
            return self::QDateTimeToPHP($settings->dateformat_code_default);
        }

        return 'Y-m-d';
    }

    public static function getDefaultDateTimeFormat() {
        if ($settings = UserAccount::getSettings()) {
            return self::QDateTimeToPHP($settings->dateformat_code_default . ' ' . $settings->timeformat_code_default);
        }

        return 'Y-m-d H:i:s';
    }

    /**
     * Date formatted
     * @param string $date
     * @return false|string
     */
    public function date($date)
    {
        $format = $this->getFormatDate();
        return ($format) ? date($format, strtotime($date)) : $date;
    }

    /**
     * DateTime formatted
     * @param $dateTime
     * @return false|string
     */
    public function dateTime($dateTime)
    {
        if ($this->dateFormat && $this->timeFormat && $this->timeZone) {
            preg_match('/\(GMT([\-|\+]\d+):\d+\)/', $this->timeZone, $matches);
            $gmt = (int)$matches[1];

            if ($this->revertFormat) {
                $format = $this->revertFormat;
                $fieldFormat = $this->getFormatDate();
                $fieldFormat .= ($format === self::getDefaultDateTimeFormat()) ? ' ' . $this->getFormatTime() : '';

                $time = \DateTime::createFromFormat($fieldFormat, $dateTime)->getTimestamp();
            } else {
                $format = $this->getFormatDate() . ' ' . $this->getFormatTime();
                $time = strtotime($dateTime);
            }

            if ($time === false) {
                $dateTime = str_replace('.', '-', $dateTime);
                $time = strtotime($dateTime);
            }
            
            if ($this->revertFormat && $format === self::getDefaultDateTimeFormat()) $time -= 3600 * $gmt;
            else if (!$this->revertFormat) $time += 3600 * $gmt;

            return date($format, $time);
        }
        return $dateTime;
    }

    /**
     * Getting date time format based on GMT zone
     * @param string $dateTime
     * @param string $format
     * @return false|string
     */
    public function revertDateTime($dateTime, $format) {
        $this->revertFormat = $format;
        return $this->run($dateTime, self::DATE_TIME_TEXT_FORMAT);
    }

    /**
     * Currency formatted
     * @param $str
     * @return string
     */
    public function currency($str)
    {
        return $this->currencyNumbers($str) . html_entity_decode('&nbsp;') . self::getCurrencySuffix();
    }

    public function decimal($str, $precision)
    {
        return $this->decimalNumbers($str, $precision);
    }

	public function phone($phoneNumber)
    {
		//echo 'in _FormatteddHelper phone function';
		//echo 'Before :: '.$phoneNumber;

		$phoneNumber = str_replace(array('-', '(', ')', ' '), '', $phoneNumber);
		//echo 'After :: '.$phoneNumber;

		if(is_numeric($phoneNumber)) {
			$phoneNumber = preg_replace('/[^0-9]/','',$phoneNumber);

			if(strlen($phoneNumber) > 10) {
				$countryCode = substr($phoneNumber, 0, strlen($phoneNumber)-10);
				$areaCode = substr($phoneNumber, -10, 3);
				$nextThree = substr($phoneNumber, -7, 3);
				$lastFour = substr($phoneNumber, -4, 4);

				$phoneNumber = '+'.$countryCode.' ('.$areaCode.') '.$nextThree.'-'.$lastFour;
			}
			else if(strlen($phoneNumber) == 10) {
				$areaCode = substr($phoneNumber, 0, 3);
				$nextThree = substr($phoneNumber, 3, 3);
				$lastFour = substr($phoneNumber, 6, 4);

				$phoneNumber = '('.$areaCode.') '.$nextThree.'-'.$lastFour;
			}
			else if(strlen($phoneNumber) == 7) {
				$nextThree = substr($phoneNumber, 0, 3);
				$lastFour = substr($phoneNumber, 3, 4);

				$phoneNumber = $nextThree.'-'.$lastFour;
			}

			return $phoneNumber;
		}
    }

    public function currencyNumbers($str)
    {
        return $this->decimalNumbers($str, 2);
    }

    public function decimalNumbers($str, $precision = 0)
    {
        if ($this->currencyFormat && is_numeric($str)) {
            preg_match('/#+([^#])#+([^#])#+/', $this->currencyFormat, $matches);
            if ($str !== '') {
                $str = number_format($str, (int) $precision, $matches[2], $matches[1]);
            }
        }

        return $str;
    }

    /**
     * Getting format of date
     * @return null|string
     */
    public function getFormatDate()
    {
        return self::QDateTimeToPHP($this->dateFormat);
    }

    /**
     * Getting format of dateTime
     * @return null|string
     */
    public function getFormatTime()
    {
        return self::QDateTimeToPHP($this->timeFormat);
    }

    /**
     * Getting format of date for datePicker plugin
     * @return mixed|null|string
     */
    public function getFormatDateForPicker()
    {
        $date = $this->getFormatDate();
        if (strpos($date, 'y') !== false) $date = str_replace('y', 'yy', $date);
        if (strpos($date, 'Y') !== false) $date = str_replace('Y', 'yyyy', $date);

        if (strpos($date, 'm') !== false) $date = str_replace('m', 'mm', $date);
        if (strpos($date, 'n') !== false) $date = str_replace('n', 'm', $date);

        if (strpos($date, 'F') !== false) $date = str_replace('F', 'MM', $date);

        if (strpos($date, 'l') !== false) $date = str_replace('l', 'DD', $date);

        if (strpos($date, 'd') !== false) $date = str_replace('d', 'dd', $date);
        if (strpos($date, 'j') !== false) $date = str_replace('j', 'd', $date);

        return $date;
    }

    /**
     * Getting format of dateTime for dateTimePicker plugin
     * @return string
     */
    public function getFormatDateTimeForPicker()
    {
        $date = $this->getFormatDateForPicker();
        $time = $this->timeFormat;

        if (strpos($time, 'mm') !== false) $time = str_replace('mm', 'ii', $time);
        if (strpos($time, 'm') !== false) $time = str_replace('m', 'i', $time);

        if (strpos($time, 'AP') !== false) $time = str_replace('AP', 'P', $time);
        if (strpos($time, 'ap') !== false) $time = str_replace('ap', 'p', $time);
        if (strpos($time, 'A') !== false) $time = str_replace('A', 'P', $time);
        if (strpos($time, 'a') !== false) $time = str_replace('a', 'p', $time);

        return $date . ' ' . $time;
    }

    public static function QDateTimeToPHP($strFormat)
    {
        preg_match_all('/(?(?=d)([d]+)|(?(?=M)([M]+)|(?(?=AP)([AP]+)|(?(?=ap)([ap]+)|(?(?=y)([y]+)|(?(?=h)([h]+)|(?(?=H)([H]+)|(?(?=m)([m]+)|(?(?=s)([s]+)|(?(?=z)([z]+)|(?(?=t)([t]+)|)))))))))))/', $strFormat, $strArray);

        $strArray = $strArray[0];
        $strToReturn = '';

        $intStartPosition = 0;
        for ($intIndex = 0; $intIndex < count($strArray); $intIndex++) {
            $strToken = trim($strArray[$intIndex]);
            if ($strToken) {
                $intEndPosition = strpos($strFormat, $strArray[$intIndex], $intStartPosition);
                $strToReturn .= substr($strFormat, $intStartPosition, $intEndPosition - $intStartPosition);
                $intStartPosition = $intEndPosition + strlen($strArray[$intIndex]);

                switch ($strArray[$intIndex]) {
                    case 'M':
                        $strToReturn .= 'n';
                        break;
                    case 'MM':
                        $strToReturn .= 'm';
                        break;
                    case 'MMM':
                        $strToReturn .= 'M';
                        break;
                    case 'MMMM':
                        $strToReturn .= 'F';
                        break;

                    case 'd':
                        $strToReturn .= 'j';
                        break;
                    case 'dd':
                        $strToReturn .= 'd';
                        break;
                    case 'ddd':
                        $strToReturn .= 'D';
                        break;
                    case 'dddd':
                        $strToReturn .= 'l';
                        break;

                    case 'yy':
                        $strToReturn .= 'y';
                        break;
                    case 'yyyy':
                        $strToReturn .= 'Y';
                        break;

                    case 'H':
                        $strToReturn .= 'g';
                        break;
                    case 'HH':
                        $strToReturn .= 'h';
                        break;
                    case 'h':
                        $strToReturn .= 'G';
                        break;
                    case 'hh':
                        $strToReturn .= 'H';
                        break;

                    case 'm':
                        $strToReturn .= 'i';
                        break;
                    case 'mm':
                        $strToReturn .= 'i';
                        break;

                    case 'ss':
                        $strToReturn .= 's';
                        break;

                    case 'z':
                        $strToReturn .= 'a';
                        break;
                    case 'zz':
                        $strToReturn .= 'A';
                        break;
                    case 'zzz':
                        $strToReturn .= sprintf('%s.m.', substr('a', 0, 1));
                        break;
                    case 'zzzz':
                        $strToReturn .= sprintf('%s.M.', substr('A', 0, 1));
                        break;

                    case 'ttt':
                        $strToReturn .= 'T';
                        break;

                    case 'AP':
                        $strToReturn .= 'A';
                        break;
                    case 'ap':
                        $strToReturn .= 'a';
                        break;

                    default:
                        $strToReturn .= $strArray[$intIndex];
                }
            }
        }

        if ($intStartPosition < strlen($strFormat))
            $strToReturn .= substr($strFormat, $intStartPosition);

        return $strToReturn;
    }
}