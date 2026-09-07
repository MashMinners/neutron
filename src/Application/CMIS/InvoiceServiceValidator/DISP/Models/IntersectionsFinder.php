<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

class IntersectionsFinder
{
    public function __construct(private BaseInvoiceXmlParser $parser){

    }

    private function assembleXlsRecords(array $xls){
        $assembledXls = [];
        $workSchema = [
            'Серия/номер полиса',
            'Дата поступления',
            'Пациент'
        ];
        //Получаю заголовок xls
        $xlsHeader = array_shift($xls);
        $excelFieldsKeys = $this->parser->getExcelFieldsKeys($workSchema, $xlsHeader);
        foreach ($xls AS $single){
            $assembledXls[$single[$excelFieldsKeys['Серия/номер полиса']]]['Полис'] = $single[$excelFieldsKeys['Серия/номер полиса']];
            $assembledXls[$single[$excelFieldsKeys['Серия/номер полиса']]]['Пациент'] = $single[$excelFieldsKeys['Пациент']];
            $assembledXls[$single[$excelFieldsKeys['Серия/номер полиса']]]['Дата поступления'] = date('d.m.Y', strtotime($single[$excelFieldsKeys['Дата поступления']]));
        }
        return $assembledXls;
    }

    private function assembleXmlRecords(array $xml){
        $assembledD = [];
        foreach ($xml['D']['ZAP'] AS $zap){
            $idPac = $zap['PACIENT'][0]['ID_PAC'];
            $enp = $zap['PACIENT'][0]['ENP'];
            $date1 = date('d.m.Y', strtotime($zap['Z_SL'][0]['DATE_Z_1']));
            $date2 = date('d.m.Y', strtotime($zap['Z_SL'][0]['DATE_Z_2']));
            $assembledD[$idPac]['ID_PAC'] = $idPac ;
            $assembledD[$idPac]['ENP'] = $enp;
            $assembledD[$idPac]['DATE_Z_1'] = $date1;
            $assembledD[$idPac]['DATE_Z_2'] = $date2;
        }
        $assembledXml = [];
        foreach ($xml['L']['PERS'] AS $pers){
            if (array_key_exists($pers['ID_PAC'], $assembledD)){
                $enp = $assembledD[$pers['ID_PAC']]['ENP'];
                $assembledXml[$enp]['FAM'] = $pers['FAM'];
                $assembledXml[$enp]['IM'] = $pers['IM'];
                $assembledXml[$enp]['OT'] = $pers['OT'];
                $assembledXml[$enp]['DR'] = date('d.m.Y', strtotime($pers['DR']));
                $assembledXml[$enp]['ENP'] = $assembledD[$pers['ID_PAC']]['ENP'];
                $assembledXml[$enp]['DATE_1'] = $assembledD[$pers['ID_PAC']]['DATE_Z_1'];
                $assembledXml[$enp]['DATE_2'] = $assembledD[$pers['ID_PAC']]['DATE_Z_2'];
            }
        }
        return $assembledXml;
    }

    private function compareXmlAndXls(array $xml, array $xls){
        $compared = [];
        $intersections = array_intersect_key($xml, $xls);
        foreach ($intersections AS $intersection){
            $enp = $intersection['ENP'];
            $compared[$enp] = $intersection;
            $compared[$enp]['DATE_STAC'] = $xls[$enp]['Дата поступления'];
        }
        return $compared;
    }

    private function getIntersectionsByDate(array $compared){
        $intersection = [];
        foreach ($compared AS $single){
            if ($single['DATE_STAC'] >= $single['DATE_1'] AND $single['DATE_STAC'] <= $single['DATE_2']){
                $intersection[] = $single;
            }
        }
        return $intersection;
    }

    public function findIntersectionsWithStac(array $files){
        $xml = $this->parser->parseXML($files);
        $xls = $this->parser->parseExcel();
        $assembledXml = $this->assembleXmlRecords($xml);
        $assembledXls = $this->assembleXlsRecords($xls);
        $compared = $this->compareXmlAndXls($assembledXml, $assembledXls);
        $intersections = $this->getIntersectionsByDate($compared);
        return $intersections;
    }

}