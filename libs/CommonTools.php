<?php
class CommonTools
{
    private static $letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", 
        "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W",
        "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j",
        "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w",
        "x", "y", "z"
    );

    private static $letterLikeChars = array("-", "-", '$', "@", ".");

    private static $digits = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");

    private static $blankChars = array("\n", "\r", "\v", "\t", " ", "\f");

    public static function arrayToText($array)
    {
        $text = implode("\n", $array);
        return $text;
    }

    public static function textToArray($text)
    {
        $text = str_replace(chr(13) . chr(10), "\n", $text);
        $text = str_replace(chr(13), "\n", $text);
        $text = str_replace(chr(10), "\n", $text);
        $temp = explode("\n", $text);
        $array = array();
        foreach($temp as $item)
        {
            $item = trim($item);
            if($item)
                $array[] = $item;
        }
        return $array;
    }

    public static function generateRandomString($length = 8, $type = "keyword") // type: keyword, password
    {
        $chars = self::$letters;
        $chars[] = "_";
        $digits = self::$digits;
        $punctuations = array("$", "-", ".");

        $randomString = "";
        $charset = array();
        if($type == "keyword")
        {
            $charset = array_merge($chars, $digits);
            $randomString = $chars[rand(0, count($chars) - 1)];
            $length--;
        }
        else
        {
            $charset = array_merge($chars, $digits, $punctuations);
        }
        for($i = 0; $i < $length; $i++)
        {
            $randomString .= $charset[rand(0, count($charset) - 1)];
        }

        return $randomString;
    }

    public static function arrayMerge($array1, $array2)
    {
        $result = $array1;
        foreach($array2 as $key => $value)
        {
            if(is_int($key))
                $result[] = $value;
            else if(!isset($result[$key]) || !is_array($result[$key]) || !is_array($value))
            {
                $result[$key] = $value;
            }
            else
            {
                $result[$key] = self::arrayMerge($result[$key], $value);
            }

            if(!is_int($key) && $result[$key] === null)
                unset($result[$key]);
        }

        return $result;
    }

    public static function isLetter($char)
    {
        return in_array($char, self::$letters);
    }

    public static function isDigit($char)
    {
        return in_array($char, self::$digits);
    }

    public static function isLetterOrDigit($char)
    {
        return self::isLetter($char) || self::isDigit($char);
    }

    public static function filterSpecialChar($string)
    {
        $newString = "";
        for($i = 0; $i < strlen($string); $i++)
        {
            if(self::isLetterOrDigit($string[$i]))
                $newString .= $string[$i];
        }
        return $newString;
    }

    public static function filterBlankChars($string)
    {
        $newString = "";
        for($i = 0; $i < strlen($string); $i++)
        {
            if(!in_array($string[$i], self::$blankChars))
                $newString .= $string[$i];
        }
        return $newString;
    }

    public static function minimumBlankChars($string)
    {
        $string = str_replace("\n", " ", $string);
        $string = str_replace("\r", " ", $string);
        $string = str_replace("\f", " ", $string);

        $newString = "";
        $lastChar = "";
        $blankChars = array(" ", "\t", "\v", "\f");
        for($i = 0; $i < strlen($string); $i++)
        {
            if(!in_array($string[$i], $blankChars) || !in_array($lastChar, $blankChars))
                $newString .= $string[$i];
            $lastChar = $string[$i];
        }
        return $newString;
    }

}
