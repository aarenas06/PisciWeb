<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Shared\Date as SharedDate;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


class PhpSpreadsheet
{
    private $Service;
    private $file;

    public function __construct()
    {
    }

    public function create($file)
    {
        $this->Service = new Spreadsheet();
        $this->file = $file;
    }

    public function load($file)
    {
        $this->Service = PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $this->file = $file;
    }

    public function getService()
    {
        return $this->Service;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function save()
    {
        $Writer = new Xlsx($this->Service);
        $Writer->save($this->file);
    }

    public function rewrite()
    {
        $Writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->Service, "Xlsx");
        $Writer->save($this->file);
    }

    public function insertImage($image, $width, $height, $cell, $name = "Logo Cliente", $description = null, $Sheet = null)
    {
        $Drawing = new MemoryDrawing();

        if (strpos($image, ".png") === false) {
            $imageType = "jpg";
        } else {
            $imageType = "png";
        }

        if ($imageType == "png") {
            $gdImage = imagecreatefrompng($image);
            imagesavealpha($gdImage, true);
            $render = PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::RENDERING_PNG;
        } else {
            $gdImage = imagecreatefromjpeg($image);
            $render = PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::RENDERING_JPEG;
        }
        $Drawing->setName($name);
        ($description == null ?: $Drawing->setDescription($description));
        $Drawing->setResizeProportional(false);
        $Drawing->setImageResource($gdImage);
        $Drawing->setRenderingFunction($render);
        $Drawing->setMimeType(PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_DEFAULT);
        $Drawing->setWidth(intval($width));
        $Drawing->setHeight($height);
        $Drawing->setCoordinates($cell);
        $Drawing->setWorksheet(($Sheet == null ? $this->Service->getActiveSheet() : $Sheet));
    }



    public function getRangeCellByMoveColumns($cell, $desplazamiento)
    {
        $rangeCell = false;
        try {
            preg_match('/([a-zA-Z]+)([0-9]+)/', $cell, $desgloseCell);
            $columnaActual = $desgloseCell[1];
            $fila = $desgloseCell[2];
            // Convertimos la letra de la columna actual a su valor numérico
            $valorColumnaActual = 0;
            foreach (str_split($columnaActual) as $letra) {
                $valorColumnaActual = $valorColumnaActual * 26 + ord($letra) - 64;
            }

            // Calculamos el valor de la nueva columna después de desplazarnos
            $nuevaColumnaValor = $valorColumnaActual + $desplazamiento;

            // Convertimos el valor numérico de la nueva columna a su letra correspondiente
            $nuevaColumnaLetra = '';
            while ($nuevaColumnaValor > 0) {
                $modulo = ($nuevaColumnaValor - 1) % 26;
                $nuevaColumnaLetra = chr($modulo + 65) . $nuevaColumnaLetra;
                $nuevaColumnaValor = intdiv($nuevaColumnaValor - $modulo - 1, 26);
            }

            // Concatenamos la letra de la nueva columna con el número de la fila para obtener la celda resultante
            $rangeCell = $columnaActual . $fila . ":" . $nuevaColumnaLetra . $fila;
        } catch (Exception $ex) {
            $error["success"] = false;
            $error["message"] = $ex->getMessage();
            return $error;
        }
        return $rangeCell;
    }

    public function getRangeCellByMoveRows($cell, $desplazamiento)
    {
        $rangeCell = false;
        try {
            preg_match('/([a-zA-Z]+)([0-9]+)/', $cell, $desgloseCell);
            $columnaActual = $desgloseCell[1];
            $fila = $desgloseCell[2];

            $rangeCell = $columnaActual . $fila . ":" . $columnaActual . ($fila + $desplazamiento);
        } catch (Exception $ex) {
            $error["success"] = false;
            $error["message"] = $ex->getMessage();
            return $error;        }
        return $rangeCell;
    }

    public function convertNumOfLetters($num)
    {
        $letras = range('A', 'Z');
        $num2 = floor($num / 26) - 1;
        if ($num > 26) {
            $aux = floor($num / 26);
            $aux = 26 * $aux;
            $numFinal = $num - $aux;
        } else {
            $numFinal = $num;
        }
        $letras = ($num2 < 0 ? null : $letras[$num2]) . $letras[$numFinal];
        return $letras;
    }

    public function getValuesLastRow($startColumn, $endColumn, $indexStart, $countLimit = null, $Sheet = null)
    {
        $data = array();
        try {
            $index = $indexStart;
            if ($countLimit !== null) {
                $countLimit += $index;
            }
            if ($Sheet == null) $Sheet = $this->Service->getActiveSheet();
            $trim = function ($list) {
                return array_map(function ($value) {
                    return ($value === null ? null : trim($value));
                }, $list);
            };
            do {
                $range = $startColumn . $index . ":" . $endColumn . $index;
                $auxData = $Sheet
                    ->rangeToArray(
                        $range,
                        null,
                        TRUE,
                        false,
                        false
                    );
                if (count(array_filter($auxData[0], function ($value) {
                    return $value !== null;
                })) == 0) {
                    if ($countLimit !== null) {
                        if ($countLimit == $index) {
                            $index = false;
                        } else {
                            $data[] = $trim($auxData[0]);
                        }
                    } else {
                        $index = false;
                    }
                } else {
                    $data[] = $trim($auxData[0]);
                }
                // Ignorar valores booleanos incompatibles con autoincrementar
                if ($index !== false) {
                    $index++;
                }
            } while ($index !== false);
        } catch (Exception $ex) {
            $error["success"] = false;
            $error["message"] = $ex->getMessage();
            return $error;
        }
        return $data;
    }

    public function getValuesLastColumn($startRow, $indexStart, $countLimit = null, $Sheet = null)
    {
        $data = array();
        try {
            if ($Sheet == null) $Sheet = $this->Service->getActiveSheet();
            $trim = function ($list) {
                return array_map(function ($value) {
                    return ($value == null ? null : trim($value));
                }, $list);
            };
            $index = 0;
            do {
                $index++;
                if ($countLimit === $index) {
                    $index = false;
                } else {
                    $range = $this->getRangeCellByMoveColumns($startRow . $indexStart, $index);
                    $auxData = $Sheet
                        ->rangeToArray(
                            $range,
                            null,
                            TRUE,
                            TRUE,
                            false
                        );
                    if (count(array_filter($auxData[0], function ($value) {
                        return $value == null;
                    })) > 0) {
                        if ($countLimit !== null) {
                            if ($countLimit == $index) {
                                $index = false;
                            } else {
                                $data = $trim($auxData[0]);
                            }
                        } else {
                            $data = $trim($auxData[0]);
                            $index = false;
                        }
                    } else {
                        $data = $trim($auxData[0]);
                    }
                }
            } while ($index !== false);
        } catch (Exception $ex) {
            $error["success"] = false;
            $error["message"] = $ex->getMessage();
            return $error;
        }
        $data = array_slice($data, 0, count($data) - 1);
        return $data;
    }

    public function excelToDateTimeObject($number)
    {
        try {
            return SharedDate::excelToDateTimeObject($number);
        } catch (Exception $ex) {
            return false;
        }
    }

    public function PHPToExcel($DateTime)
    {
        try {
            return SharedDate::PHPToExcel($DateTime);
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Establece un ancho fijo a la columna a partir de una referencia de celda.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param string $cellReference  Referencia de celda (ej. "F6")
     * @param float $width           Ancho deseado
     */
    public function setColumnFixedWidth($sheet, string $cellReference, float $width): void
    {
        list($column, $row) = Coordinate::coordinateFromString($cellReference);
        $sheet->getColumnDimension($column)->setWidth($width);
    }
}