<?
namespace App\Helper;

/**
 * FormatHelper
 *
 */
class FormatHelper
{

    private $lang;

    private $options;

    /**
     * FormatHelper constructor.
     */
    public function __construct($options, $lang)
    {
        $this->options = $options;
        $this->setLang($lang);
    }

    public function currency($price)
    {
        $formatted = self::number($price, 2);
        $currency  = $this->options[$this->lang]['currency'];

        return ($currency === null) ? $formatted : $formatted . ' ' . $currency;
    }

    public function currency3dec($price)
    {
        $formatted = self::number($price, 3);
        $currency  = $this->options[$this->lang]['currency'];

        return ($currency === null) ? $formatted : $formatted . ' ' . $currency;
    }

    /**
     * Formats number (integer or float) SK, CZ locale default
     *
     * @param mixed  $number
     * @param int    $decimals      number of decimal places
     * @param string $decPoint      decimal separator
     * @param string $thoudsandsSep thousands separator
     * @param bool   $trimZeros     trim right zeros from float numbers?
     *
     * @return string
     */
    public function number($number, $decimals = 2, $decPoint = '.', $thoudsandsSep = " ", $trimZeros = false)
    {
        $fixed = self::fixFloat($number);
        if (strpos($fixed, '.') === false) {
            //int
            $number = (int)$number;

            return number_format($number, 0, $decPoint, $thoudsandsSep);
        } else {
            //float
            $number = (float)$fixed;

            return ($trimZeros) ? rtrim(rtrim(number_format($number, $decimals, $decPoint, $thoudsandsSep), '0'), $decPoint) : number_format($number, $decimals, $decPoint, $thoudsandsSep);
        }
    }

    /**
     * Unifies float numbers. All "," are replaced with "."
     *
     * @param string $float
     *
     * @return string
     */
    public function fixFloat($float)
    {
        return str_replace(',', '.', $float);
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param mixed $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

}