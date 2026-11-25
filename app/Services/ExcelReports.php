<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use App\Support\DateRange;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Replaces legacy invoice-excel.php and user-report-excel.php (PHPExcel).
 */
class ExcelReports
{
    private const MONEY = '$#,##0.00';

    public function customerInvoice(Customer $customer, Collection $orders): StreamedResponse
    {
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet()->setTitle('Invoice');

        if ($orders->isEmpty()) {
            $sheet->setCellValue('A1', 'No record Found.');
        } else {
            $sheet->mergeCells('A1:D1');
            $sheet->mergeCells('A2:C2');
            $sheet->setCellValue('A1', $customer->websiteName());
            $sheet->setCellValue('A2', 'COMMERCIAL INVOICE');
            $sheet->setCellValue('D2', 'Original');
            foreach (['A1', 'A2', 'D2'] as $cell) {
                $sheet->getStyle($cell)->getFont()->setBold(true)->setSize(20);
            }
            $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $this->labels($sheet, [
                'A3' => ['Contact Person', $customer->ContactPerson],
                'C3' => ['INVOICE No.', $this->invoiceNumber($customer)],
                'A4' => ['Company Name', $customer->CompanyName],
                'C4' => ['DATE:', now()->format('m-d-Y')],
                'A5' => ['Address', $customer->Address],
                'C5' => ['City', $customer->City],
                'A6' => ['State', $customer->State],
                'C6' => ['Zip code', $customer->Zip_Code],
                'A7' => ['Country', $customer->Country],
            ]);
            $sheet->mergeCells('B7:D7');

            $this->widths($sheet, ['A' => 15, 'B' => 40, 'C' => 15, 'D' => 15]);
            $row = $this->tableHeader($sheet, 8, ['Date', 'Design Name', 'No. of Stiches', 'Price']);

            $total = 0.0;
            foreach ($orders as $order) {
                $sheet->setCellValue("A$row", $order->Effective_Date?->format('m-d-Y'));
                $sheet->setCellValue("B$row", $order->Design_Name);
                $sheet->setCellValue("C$row", (int) $order->No_Of_Stiches);
                $sheet->setCellValue("D$row", (float) $order->Order_Price);
                $sheet->getStyle("D$row")->getNumberFormat()->setFormatCode(self::MONEY);
                $sheet->getStyle("B$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $total += (float) $order->Order_Price;
                $row++;
            }
            $this->grandTotal($sheet, $row, $total);
        }

        $name = Str::slug($customer->websiteName().'_'.$customer->ContactPerson, '_').'_Invoice_'.now()->format('m-d-Y').'.xlsx';

        return $this->download($book, $name);
    }

    public function userReport(User $user, Collection $orders, DateRange $range, bool $showPrices): StreamedResponse
    {
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet()->setTitle('Performance');

        if ($orders->isEmpty()) {
            $sheet->setCellValue('A1', 'No record Found.');
        } else {
            $sheet->mergeCells('A1:D1');
            $sheet->setCellValue('A1', $user->User_Name.' Performance Report');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);

            $this->labels($sheet, [
                'A3' => ['Name', $user->full_name],
                'C3' => ['From', $range->from->format('m-d-Y')],
                'A4' => ['Role', $user->role_name],
                'C4' => ['To', $range->to->format('m-d-Y')],
                'A5' => ['Joining Date', $this->joiningDate($user)],
                'C5' => ['Profit Distribution', $user->Profit_Distribution],
                'A6' => ['Phone', $user->Contact_No],
                'C6' => ['Email', $user->Email],
            ]);

            $this->widths($sheet, ['A' => 15, 'B' => 40, 'C' => 20, 'D' => 25]);
            $headers = ['Date', 'Design Name', 'No of Stiches'];
            if ($showPrices) {
                $headers[] = 'Price';
            }
            $row = $this->tableHeader($sheet, 8, $headers);

            $total = 0.0;
            foreach ($orders as $order) {
                $sheet->setCellValue("A$row", $order->Completed_Date?->format('m-d-Y'));
                $sheet->setCellValue("B$row", $order->Design_Name);
                $sheet->setCellValue("C$row", (int) $order->No_Of_Stiches);
                if ($showPrices) {
                    $sheet->setCellValue("D$row", (float) $order->Order_Price);
                    $sheet->getStyle("D$row")->getNumberFormat()->setFormatCode(self::MONEY);
                }
                $total += (float) $order->Order_Price;
                $row++;
            }
            if ($showPrices) {
                $this->grandTotal($sheet, $row, $total);
            } else {
                $sheet->setCellValue("B$row", 'Total designs: '.$orders->count());
                $sheet->getStyle("B$row")->getFont()->setBold(true);
            }
        }

        $name = Str::slug($user->User_Name, '_').'_Report_'.now()->format('m-d-Y').'.xlsx';

        return $this->download($book, $name);
    }

    public function invoiceNumber(Customer $customer): string
    {
        return $customer->websitePrefix().$customer->Customer_ID.'/'.now()->format('m/y');
    }

    private function joiningDate(User $user): string
    {
        $date = (string) $user->Joining_Date;

        return ($date === '' || str_starts_with($date, '0000')) ? '' : date('m-d-Y', strtotime($date));
    }

    private function labels(Worksheet $sheet, array $cells): void
    {
        foreach ($cells as $cell => [$label, $value]) {
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $valueCell = chr(ord($cell[0]) + 1).substr($cell, 1);
            $sheet->setCellValueExplicit($valueCell, (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
    }

    private function widths(Worksheet $sheet, array $widths): void
    {
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
    }

    private function tableHeader(Worksheet $sheet, int $row, array $headers): int
    {
        foreach ($headers as $i => $title) {
            $cell = chr(ord('A') + $i).$row;
            $sheet->setCellValue($cell, $title);
            $sheet->getStyle($cell)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return $row + 1;
    }

    private function grandTotal(Worksheet $sheet, int $row, float $total): void
    {
        $sheet->getStyle("A$row:D$row")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->setCellValue("C$row", 'Grand Total:');
        $sheet->setCellValue("D$row", $total);
        $sheet->getStyle("C$row:D$row")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("D$row")->getNumberFormat()->setFormatCode(self::MONEY);
    }

    private function download(Spreadsheet $book, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($book) {
            (new Xlsx($book))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-store',
        ]);
    }
}
