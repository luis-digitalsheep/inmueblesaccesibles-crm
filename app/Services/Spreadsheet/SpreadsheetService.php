<?php

namespace App\Services\Spreadsheet;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class SpreadsheetService {
  /**
   * Lee datos de un archivo y los devuelve como un array.
   *
   * @param string $filePath La ruta al archivo.
   * @param int $sheetIndex El índice de la hoja a leer (0 por defecto).
   * @param bool $skipHeader Si se debe omitir la primera fila (cabecera).
   * @return array Array de filas, donde cada fila es un array de celdas.
   * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
   */
  public function readSpreadsheet(string $filePath, int $sheetIndex = 0, bool $skipHeader = true): array {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheet($sheetIndex);

    // Convertir la hoja a un array, omitiendo filas vacías al final
    // y obteniendo los valores calculados de las fórmulas.
    $dataArray = $sheet->toArray(null, true, true, true);

    if ($skipHeader && !empty($dataArray)) {
      array_shift($dataArray);
    }

    return $dataArray;
  }

  /**
   * Genera un objeto Spreadsheet a partir de un array de datos.
   *
   * @param array $datos Array de datos para popular la hoja. La primera sub-array puede ser la cabecera.
   * @param string $tituloHoja Título de la hoja.
   * @return Spreadsheet El objeto Spreadsheet poblado.
   */
  public function generateSpreadsheet(array $datos, array $cabeceras = [], string $tituloHoja = 'Hoja1'): Spreadsheet {
    $spreadsheet = new Spreadsheet();

    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($tituloHoja);

    $filaActual = 1;

    // Escribir cabeceras si se proporcionan
    if (!empty($cabeceras)) {
      $columna = 'A';

      foreach ($cabeceras as $textoCabecera) {
        $sheet->setCellValue($columna . $filaActual, $textoCabecera);

        // Aplicar estilo a la cabecera (opcional)
        $sheet->getStyle($columna . $filaActual)->getFont()->setBold(true);
        $sheet->getStyle($columna . $filaActual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $columna++;
      }

      $filaActual++;
    }

    // Escribir datos
    foreach ($datos as $filaDatos) {
      $columna = 'A'; // Reinicia la columna para cada fila

      foreach ($filaDatos as $valorCelda) {
        $sheet->setCellValue($columna . $filaActual, $valorCelda);
        $columna++;
      }

      $filaActual++;
    }

    // Autoajustar ancho de columnas (opcional, puede ser costoso en rendimiento para muchos datos)
    // $columnaIterator = $sheet->getColumnIterator();
    // foreach ($columnaIterator as $column) {
    //     $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
    // }

    return $spreadsheet;
  }

  /**
   * Envía un objeto Spreadsheet al navegador para descarga.
   *
   * @param Spreadsheet $spreadsheet El objeto Spreadsheet a enviar.
   * @param string $nombreArchivo El nombre del archivo para la descarga.
   */
  public function downloadSpreadsheet(Spreadsheet $spreadsheet, string $nombreArchivo = 'reporte.xlsx'): void {
    if (ob_get_level()) {
      ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }
}
