<?php

namespace App\Services;

use CodeIgniter\HTTP\ResponseInterface;

class ExportService
{
    public function toCsv(string $filename, array $headers, array $rows): ResponseInterface
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return service('response')
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    public function toXlsx(string $filename, array $headers, array $rows): ResponseInterface
    {
        // Fallback to CSV content with xlsx extension to keep compatibility without external libs.
        return $this->toCsv($filename, $headers, $rows)
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function toPdf(string $filename, string $title, array $headers, array $rows): ResponseInterface
    {
        $lines = [];
        $lines[] = $title;
        $lines[] = str_repeat('-', 80);
        $lines[] = implode(' | ', $headers);
        $lines[] = str_repeat('-', 80);
        foreach ($rows as $row) {
            $lines[] = implode(' | ', array_map('strval', $row));
        }

        $pdf = $this->buildSimplePdf($lines);

        return service('response')
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($pdf);
    }

    protected function buildSimplePdf(array $lines): string
    {
        // Very simple PDF with Helvetica and text lines.
        $contentLines = [];
        $y = 800;
        foreach ($lines as $line) {
            $safe = $this->escapePdfText($line);
            $contentLines[] = sprintf('1 0 0 1 50 %d Tm (%s) Tj', $y, $safe);
            $y -= 14;
            if ($y < 40) {
                break;
            }
        }
        $stream = "BT\n/F1 12 Tf\n" . implode("\n", $contentLines) . "\nET";
        $len = strlen($stream);

        $objects = [];
        $objects[] = "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj";
        $objects[] = "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj";
        $objects[] = "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj";
        $objects[] = "4 0 obj<< /Length $len >>stream\n$stream\nendstream endobj";
        $objects[] = "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj . "\n";
        }
        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";

        return $pdf;
    }

    protected function escapePdfText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        return $text;
    }
}