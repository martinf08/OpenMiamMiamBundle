<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Document\Excel\Producer;

use Isics\Bundle\OpenMiamMiamBundle\Document\Excel\Tools;
use Isics\Bundle\OpenMiamMiamBundle\Model\Document\ProducersTransfer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Symfony\Component\Translation\TranslatorInterface;

class TransferExcel
{
    /**
     * @var Spreadsheet
     */
    protected $excel;

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    protected $translator;

    /**
     * @var string $currency
     */
    protected $currency;

    /**
     * Constructor
     *
     * @param Spreadsheet                               $excel
     * @param \Symfony\Component\Translation\Translator $translator
     */
    public function __construct(Spreadsheet $excel, TranslatorInterface $translator, $currency)
    {
        $this->excel      = $excel;
        $this->translator = $translator;
        $this->currency = $currency;
    }

    /**
     * Build document
     *
     * @param ProducersTransfer $producersTransfer
     */
    public function generate(ProducersTransfer $producersTransfer)
    {
        $this->excel->setActiveSheetIndex(0);
        $sheet = $this->excel->getActiveSheet();
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        $formatter = new \NumberFormatter($this->translator->getLocale(), \NumberFormatter::CURRENCY);
        $intl = new \IntlDateFormatter($this->translator->getLocale(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMMM yyyy');

        $boldStyle = array(
            'font' => array(
                'bold' => true,
            )
        );

        $centerStyle = array(
            'alignment'=>array(
                'horizontal' => Alignment::HORIZONTAL_CENTER
            )
        );

        $rightStyle = array(
            'alignment'=>array(
                'horizontal' => Alignment::HORIZONTAL_RIGHT
            )
        );

        $borderStyle = array(
            'borders' => array(
                'outline' => array(
                    'style' => Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            )
        );

        // Branch occurrence name and date
        $branchOccurrenceNumber = 1;

        foreach ($producersTransfer->getBranchOccurrences() as $branchOccurrence) {
            // branch occurence Name
            $sheet->setCellValue(
                Tools::getColumnNameForNumber($branchOccurrenceNumber).'2',
                $branchOccurrence->getBranch()->getName()
            );

            // branch occurence Date
            $sheet->setCellValue(
                Tools::getColumnNameForNumber($branchOccurrenceNumber).'3',
                $branchOccurrence->getEnd()->format('d/m/Y')
            );
            // branch occurence group style
            $sheet->getStyle(
                Tools::getColumnNameForNumber($branchOccurrenceNumber).'2:'.
                Tools::getColumnNameForNumber($branchOccurrenceNumber).'3'
            )->applyFromArray(array_merge($borderStyle, $centerStyle, $boldStyle));

            ++$branchOccurrenceNumber;
        }

        // column number for total
        $columnNumber = $branchOccurrenceNumber;

        // Merge cells for document title
        $sheet->mergeCells('B1:'.Tools::getColumnNameForNumber($branchOccurrenceNumber).'1');
        $sheet->setCellValue(
            'B1',
            ucfirst($intl->format($producersTransfer->getDate()))."\n\n".
            $this->translator->trans('excel.association.producer.transfer.title')
        );
        $sheet->getStyle('B1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B1')->applyFromArray(array_merge($centerStyle, $boldStyle));
        $sheet->getRowDimension('1')
            ->setRowHeight(50);

        // title of producer total column
        $sheet->setCellValue(
            Tools::getColumnNameForNumber($branchOccurrenceNumber).'2',
            $this->translator->trans('excel.association.producer.transfer.total')
        );
        $sheet->getStyle(
            Tools::getColumnNameForNumber($branchOccurrenceNumber).'2:'.
            Tools::getColumnNameForNumber($branchOccurrenceNumber).'3'
        )->applyFromArray(array_merge($borderStyle, $centerStyle, $boldStyle));


        $currentLine = 4;

        foreach ($producersTransfer->getProducers() as $producer) {
            // producer name column
            $sheet->setCellValue(
                'A'.(string)$currentLine,
                $producer->getName()
            );
            $sheet->getStyle(
                'A'.(string)$currentLine
            )->applyFromArray(array_merge($borderStyle, $boldStyle));

            $branchOccurrenceNumber = 1;

            foreach ($producersTransfer->getBranchOccurrences() as $branchOccurrence) {
                $value = $producersTransfer->getAmount($producer->getId(), $branchOccurrence->getId());

                if ($value == 0.0) {
                    $value = null;
                }

                // total for producer by branch occurence
                $sheet->setCellValue(
                    Tools::getColumnNameForNumber($branchOccurrenceNumber).(string)$currentLine,
                    null !== $value ? $formatter->formatCurrency($value,$this->currency) : '-'
                );
                $sheet->getStyle(
                    Tools::getColumnNameForNumber($branchOccurrenceNumber).(string)$currentLine
                )->applyFromArray(array_merge($borderStyle, $rightStyle));

                ++$branchOccurrenceNumber;
            }

            // Total by producer
            $sheet->setCellValue(
                Tools::getColumnNameForNumber($branchOccurrenceNumber).(string)$currentLine,
                $formatter->formatCurrency($producersTransfer->getTotalForProducerId($producer->getId()),$this->currency)
            );
            $sheet->getStyle(
                Tools::getColumnNameForNumber($branchOccurrenceNumber).(string)$currentLine
            )->applyFromArray(array_merge($borderStyle, $boldStyle, $rightStyle));

            ++$currentLine;
        }

        // style of TOTAL cell
        $sheet->getStyle(
            Tools::getColumnNameForNumber($branchOccurrenceNumber).(string)$currentLine
        )->applyFromArray(array_merge($borderStyle, $boldStyle, $rightStyle));

        // title for branch occurrence total
        $sheet->setCellValue(
            'A'.(string)$currentLine,
            $this->translator->trans('excel.association.producer.transfer.total')
        );
        $sheet->getStyle(
            'A'.(string)$currentLine
        )->applyFromArray(array_merge($borderStyle, $boldStyle));

        // Current column number of branch occurence
        $branchOccurrenceNumber = 1;

        foreach ($producersTransfer->getBranchOccurrences() as $branchOccurrence) {

            $value = $producersTransfer->getTotalForBranchOccurrenceId($branchOccurrence->getId());

            if ($value == 0.0) {
                $value = null;
            }

            // branch occurrence total
            $sheet->setCellValue(
                Tools::getColumnNameForNumber($branchOccurrenceNumber).(string)$currentLine,
                null !== $value ? $formatter->formatCurrency($value,$this->currency) : '-'
            );
            $sheet->getStyle(
                Tools::getColumnNameForNumber($branchOccurrenceNumber).(string)$currentLine
            )->applyFromArray(array_merge($borderStyle, $boldStyle, $rightStyle));

            ++$branchOccurrenceNumber;
        }

        // producer total
        $sheet->setCellValue(
            Tools::getColumnNameForNumber($branchOccurrenceNumber).(string)$currentLine,
            $formatter->formatCurrency(array_sum($producersTransfer->getTotalByProducers()),$this->currency)
        );

        // set width auto
        $columnID = 'A';
        $lastColumn = $sheet->getHighestColumn();
        do {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            $columnID++;
        } while ($columnID != $lastColumn);
    }

    /**
     * Get excel
     *
     * @return Spreadsheet
     */
    public function getExcel()
    {
        return $this->excel;
    }

}
