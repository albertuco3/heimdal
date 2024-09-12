<?php

namespace App\Service;

use App\Repository\ArticleRepository;
use Develia\ArrayManipulator;

class XGestService {

    private XGestRestClient $xgestClient;
    private ArticleRepository $articleRepository;

    public function __construct(XGestRestClient $xgestClient, ArticleRepository $articleRepository) {

        $this->xgestClient = $xgestClient;
        $this->articleRepository = $articleRepository;
    }

    public function getImportableByOrder(string $number, string $company): ?array {

        $deliveryNote = $this->xgestClient->getDeliveryNote($number, $company);

        if ($deliveryNote == null) {
            return $deliveryNote;
        }

        return from($this->removeImportedItems([$deliveryNote]))->first();

    }

    private function removeImportedItems($importables) {

        $queryBuilder = $this->articleRepository->createQueryBuilder("a")
                                                ->select("a.deliveryNoteId, a.lineNumber")
                                                ->distinct()
                                                ->where("1 != 1");
        $i = 0;
        foreach (from($importables)->mapMany(fn($x) => $x["lines"])->values() as $importable) {


            $queryBuilder = $queryBuilder->orWhere("(a.lineNumber = :lineNumber" . $i . " and a.deliveryNoteId = :deliveryNoteId" . $i . ")")
                                         ->setParameter("lineNumber" . $i, $importable["lineNumber"])
                                         ->setParameter("deliveryNoteId" . $i, $importable["deliveryNoteId"]);

            $i++;
        }

        $ids = $queryBuilder->getQuery()->getResult();


        foreach ($importables as $importable) {

            $importable["lines"] = from($importable["lines"])->filter(function ($x) use ($ids) {
                return !from($ids)->any(fn($y) => $y["lineNumber"] == $x["lineNumber"] && $y["deliveryNoteId"] == $x["deliveryNoteId"]);
            })->values()->toVector();

            if (count($importable["lines"]) > 0) {
                yield $importable;
            }
        }

    }

    private function getImportablesFromWarehouse($company, $warehouseId) {
        return from($this->xgestClient->getStock([$warehouseId], $company))->mapMany(function ($x) use ($warehouseId, $company) {

            if ($x) {
                foreach ($x["serial_numbers"] as $serial_number) {
                    yield [
                        'orderNumber'    => null,
                        'deliveryNoteId' => null,
                        'total'          => null,
                        'customerName'   => null,
                        'customerCode'   => null,
                        'companyName'    => $x["company_name"],
                        'companyId'      => $x["company_id"],
                        'warehouseId'    => $warehouseId,
                        'lines'          => [
                            [
                                'lineNumber'         => null,
                                'articleDescription' => null,
                                'articleCode'        => $x["article_code"],
                                'serialNumber'       => $serial_number,
                                'quantity'           => 1,
                                'taxBase'            => null,
                                'taxRate'            => null,
                                'warehouseId'        => $warehouseId,
                                'deliveryNoteId'     => null,
                            ]
                        ]
                    ];
                }
            }

        });
    }

    public function getImportables($source, ?\DateTimeInterface $fromDate, ?\DateTimeInterface $toDate, $company, $orderType): array {

        $importables = from([]);
        if (!$source || $source == 'orders') {
            $importables = $importables->union($this->xgestClient->getDeliveryNotes($fromDate, $toDate, $company));
        }
        if (!$source || $source == 'warehouse-27') {
            $importables = $importables->union($this->getImportablesFromWarehouse($company, 27));
        }

        if (!$source || $source == 'warehouse-15') {
            $importables = $importables->union($this->getImportablesFromWarehouse($company, 15));
        }


        return from($this->removeImportedItems($importables))->map(function ($importable) use ($orderType) {

            if ($orderType == 1) {
                $importable["lines"] = from($importable["lines"])->filter(fn($x) => preg_match("/^\d+$/i", $x["serialNumber"]))->toVector();
            } else if ($orderType == 2) {
                $importable["lines"] = from($importable["lines"])->filter(fn($x) => !preg_match("/^\d+$/i", $x["serialNumber"]))->toVector();
            }

            return $importable;

        })->filter(fn($x) => count($x["lines"]) > 0)->toVector();


    }

}