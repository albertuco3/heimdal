<?php

namespace App\Service;

use App\Repository\JobTypeRepository;
use App\Repository\UserRepository;
use Develia\Collections\CacheDictionary;
use Develia\Collections\DefaultDictionary;
use Develia\Collections\Dictionary;
use Develia\Obj;
use Develia\Str;
use Develia\TimeSpan;
use MongoDB\BSON\Timestamp;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelGenerator {

    private BoxService $boxService;
    private JobTypeRepository $jobTypeRepository;
    private UserRepository $userRepository;

    function __construct(BoxService $boxService, JobTypeRepository $jobTypeRepository,UserRepository $userRepository) {

        $this->boxService = $boxService;
        $this->jobTypeRepository = $jobTypeRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param int $technicianId
     * @param \DateTimeInterface|null $fromDate
     * @param \DateTimeInterface|null $toDate
     * @return never-return
     */
    public function generate_performance_excel(int $technicianId, \DateTimeInterface $fromDate = null, \DateTimeInterface $toDate = null) {
        $performance = $this->boxService->getPerformance($technicianId, $fromDate, $toDate);

        $spreadsheet = new Spreadsheet();
        $jobNameCache = new DefaultDictionary(fn($id) => $this->jobTypeRepository->find($id)?->getDescription());
        $sheet = $spreadsheet->getActiveSheet();





        $row = 0;
        foreach ($performance as $date => $jobs) {

            if($date == "total")
                continue;

            foreach ($jobs as $jobId => $data) {


                $points = $data["points"];
                $time = $data["time"];
                $averageTime = $data["averageTime"];

                $jobName = $jobNameCache[$jobId];

                if($jobName){
                    $sheet->setCellValue(ExcelGenerator::cell(1, $row), $date);
                    $sheet->setCellValue(ExcelGenerator::cell(2, $row), $points);
                    $sheet->setCellValue(ExcelGenerator::cell(3, $row), TimeSpan::fromSeconds($time)->format("mm:ss"));
                    $sheet->setCellValue(ExcelGenerator::cell(4, $row), TimeSpan::fromSeconds($averageTime)->format("mm:ss"));
                    $sheet->setCellValue(ExcelGenerator::cell(5, $row), $jobName);

                    $row++;
                }


            }


        }





        $fullName = $this->userRepository->find($technicianId)->getFullName();
        $fullName = Str::replace($fullName," ","_");
        $fromDateStr = $fromDate ? "_from_" . $fromDate->format("Y-m-d") : "";
        $toDateStr = $toDate ? "_to_" . $toDate->format("Y-m-d") : "";
        $filename = "rendimiento_". $fullName . $fromDateStr . $toDateStr .".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;

    }

    private static function cell($column, $row) {
        $columnName = '';
        while ($column > 0) {
            $modulo = ($column - 1) % 26;
            $columnName = chr(65 + $modulo) . $columnName;
            $column = (int)(($column - $modulo) / 26);
        }
        return $columnName . $row;
    }
}