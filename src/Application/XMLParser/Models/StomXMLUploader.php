<?php

namespace Application\XMLParser\Models;

use Engine\Database\IConnector;

class StomXMLUploader
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function lmUpload(array $lmPers){
        $query = ("INSERT INTO stom_xml_lm (stom_xml_lm__id_pac, stom_xml_lm_fam, stom_xml_lm_im, stom_xml_lm_ot, 
                                            stom_xml_lm_w, stom_xml_lm_dr, stom_xml_lm_snils, 
                                            stom_xml_lm_okatog, stom_xml_lm_okatop) 
                   VALUES ");
        foreach ($lmPers AS $row) {
            $query .= (" ('{$row['ID_PAC']}', '{$row['FAM']}', '{$row['IM']}', '{$row['OT']}',  '{$row['W']}', '{$row['DR']}', 
                          '{$row['SNILS']}', '{$row['OKATOG']}', '{$row['OKATOP']}'),");
        };
        //Вставляем данные в БД
        $query = substr($query,0,-1);
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return true;
    }
    private function pmUpload(array $pmSL){
        $slQuery = ("INSERT INTO stom_xml_pm_sl (stom_xml_pm_sl_sl_id, stom_xml_pm_sl_idcase, stom_xml_pm_sl_card, 
                                 stom_xml_pm_sl_purp, stom_xml_pm_sl_visit_pol, stom_xml_pm_sl_visit_hom) 
                     VALUES ");
        $stomQuery = ("INSERT INTO stom_xml_pm_sl_stom (stom_xml_pm_sl_stom_sl_id, stom_xml_pm_sl_stom_idstom, 
                                   stom_xml_pm_sl_stom_code_usl, stom_xml_pm_sl_stom_zub, stom_xml_pm_sl_stom_kol_viz, 
                                   stom_xml_pm_sl_stom_uet_fakt) 
                       VALUES ");
        foreach ($pmSL AS $row){
            $slQuery .= (" ('{$row['SL_ID']}', '{$row['IDCASE']}', '{$row['CARD']}', '{$row['PURP']}', 
                            '{$row['VISIT_POL']}', '{$row['VISIT_HOM']}'),");
            foreach ($row['STOM'] AS $stom){
                $stomQuery .= (" ('{$stom['SL_ID']}', '{$stom['IDSTOM']}', '{$stom['CODE_USL']}', '{$stom['ZUB']}', 
                                  '{$stom['KOL_VIZ']}', '{$stom['UET_FAKT']}'),");
            }
        }
        //Заливка в БД случаев
        $slQuery = substr($slQuery,0,-1);
        $stmt = $this->pdo->prepare($slQuery);
        $stmt->execute();
        //Заливка в БД услуг
        $stomQuery = substr($stomQuery,0,-1);
        $stmt = $this->pdo->prepare($stomQuery);
        $stmt->execute();
    }

    private function hmUpload(array $hmZap){
        $setStatements = '';
        $ids = [];
        foreach ($hmZap AS $zap) {
            $setStatements .= "WHEN stom_xml_lm__id_pac = {$zap['PACIENT'][0]['ID_PAC']} THEN '{$zap['PACIENT'][0]['ENP']}'";
            $ids[] = $zap['PACIENT'][0]['ID_PAC'];
        }
        $idsString = implode(',', $ids);
        $queryPacient = ("UPDATE stom_xml_lm SET stom_xml_lm_enp = CASE $setStatements END WHERE stom_xml_lm__id_pac IN ($idsString)");

        $queryZSL = ("");
        $querySL = ("");
        $queryUSL = ("");
        $stmt = $this->pdo->prepare($queryPacient);
        $stmt->execute();
        return true;
    }



    public function upload(array $registry){
        $result = [];
        /*if($this->lmUpload($registry['LM'])){
            $result['lmMessage'] = 'LM залит';
        }*/
        //$this->pmUpload($registry['PM']);
        $this->hmUpload($registry['HM']);
        return $result;
    }

}