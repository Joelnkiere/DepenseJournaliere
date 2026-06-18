<?php
namespace App\Helpers;

class ExportHelper
{
    public static function generateCSV(array $headers, array $data, string $filename): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers);
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    public static function generatePDF(string $title, array $data, string $filename): void
    {
        $html = self::buildPDFHtml($title, $data);
        $pdfFile = tempnam(sys_get_temp_dir(), 'pdf_') . '.html';
        file_put_contents($pdfFile, $html);

        header('Content-Type: text/html; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}.html\"");
        readfile($pdfFile);
        unlink($pdfFile);
        exit;
    }

    private static function buildPDFHtml(string $title, array $data): string
    {
        $rows = '';
        foreach ($data as $row) {
            $rows .= '<tr><td>' . implode('</td><td>', array_map('htmlspecialchars', $row)) . '</td></tr>';
        }
        $headers = !empty($data) ? array_keys($data[0]) : [];
        $headerHtml = '';
        foreach ($headers as $h) {
            $headerHtml .= '<th>' . htmlspecialchars($h) . '</th>';
        }

        return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8">
<style>
  body { font-family: Arial, sans-serif; margin: 20px; }
  h1 { color: #333; }
  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
  th { background-color: #f4f4f4; }
</style>
</head><body>
<h1>{$title}</h1>
<table><thead><tr>{$headerHtml}</tr></thead><tbody>{$rows}</tbody></table>
</body></html>
HTML;
    }
}