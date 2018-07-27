<?php
class CSVTool
{
    private $data = "";
    private $fileName = "";

    function CSVTool($data, $fileName)
    {
        
    }

    public static function escape($value)
    {
        if($value == "")
            $value = "";
        $value = str_replace("\n", "", $value);
        $value = str_replace("\r", "", $value);
        $value = str_replace('"', '""', $value);
        if(strpos($value, ",") >= 0 && strpos($value, ",") !== false)
            $value = '"' . $value . '"';

        return $value;
    }

    public function export()
    {
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment;filename=\"{$this->filename}.csv\""); 

        echo chr(239) . chr(187) . chr(191);
        for($i = 0; $i < count($this->data); $i++)
        {
            $row = "";
            for($j = 0; $j < count($this->data[$i]); $j++)
            {
                if($row != "")
                    $row .= ",";
                $row .= self::escape($this->data[$i][$j]);
            }

            if($row != "" && $row != count($data) - 1)
                $row .= ",\r\n";
            echo $row;
        }
    }

}
