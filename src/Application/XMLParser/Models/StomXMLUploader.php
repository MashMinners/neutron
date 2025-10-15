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
        $stomQuery = ("INSERT INTO stom_xml_pm_sl_stom (stom_xml_pm_sl_stom_sl_id, stom_xml_pm_sl_stom_sl_idcase, 
                                   stom_xml_pm_sl_stom_idstom, stom_xml_pm_sl_stom_code_usl, stom_xml_pm_sl_stom_zub, 
                                   stom_xml_pm_sl_stom_kol_viz, stom_xml_pm_sl_stom_uet_fakt) 
                       VALUES ");
        foreach ($pmSL AS $row){
            $slQuery .= (" ('{$row['SL_ID']}', '{$row['IDCASE']}', '{$row['CARD']}', '{$row['PURP']}', 
                            '{$row['VISIT_POL']}', '{$row['VISIT_HOM']}'),");
            foreach ($row['STOM'] AS $stom){
                $stomQuery .= (" ('{$stom['SL_ID']}', '{$stom['IDCASE']}', '{$stom['IDSTOM']}', '{$stom['CODE_USL']}', 
                                  '{$stom['ZUB']}', '{$stom['KOL_VIZ']}', '{$stom['UET_FAKT']}'),");
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

    /**
     * Метод подготавливает запос на обновление номеров полиса в таблицу stom_xml_lm, так как изначально полисов для
     * этих пациентов нет в файл LM.xml
     * @param array $hmZap
     * @return string
     */
    private function makeHmPacientQuery(array $hmZap) : string{
        $setStatements = '';
        $ids = [];
        foreach ($hmZap AS $zap) {
            $setStatements .= "WHEN stom_xml_lm__id_pac = {$zap['PACIENT'][0]['ID_PAC']} THEN '{$zap['PACIENT'][0]['ENP']}'";
            $ids[] = $zap['PACIENT'][0]['ID_PAC'];
        }
        $idsString = implode(',', $ids);
        $queryPacient = ("UPDATE stom_xml_lm SET stom_xml_lm_enp = CASE $setStatements END WHERE stom_xml_lm__id_pac IN ($idsString)");
        return $queryPacient;
    }

    private function makeZSLQuery(array $hmZap): string {

    }

    private function hmUpload(array $hmZap){

        $queryPacient = $this->makeHmPacientQuery($hmZap);
        $queryZSL = ("INSERT INTO stom_xml_hm_zsl(stom_xml_hm_zsl_idcase, stom_xml_hm_zsl_usl_ok, stom_xml_hm_zsl_vidpom, 
                                  stom_xml_hm_zsl_for_pom, stom_xml_hm_zsl_date_z_1, stom_xml_hm_zsl_date_z_2, 
                                  stom_xml_hm_zsl_rslt, stom_xml_hm_zsl_ishod, stom_xml_hm_zsl_idsp, stom_xml_hm_zsl_oplata, 
                                  stom_xml_hm_zsl_id_pac) 
                      VALUES ");
        $querySL = ("INSERT INTO stom_xml_hm_zsl_sl (stom_xml_hm_zsl_sl_sl_id, stom_xml_hm_zsl_sl_profil,
                                 stom_xml_hm_zsl_sl_det, stom_xml_hm_zsl_sl_p_cel, stom_xml_hm_zsl_sl_nhistory, 
                                 stom_xml_hm_zsl_sl_date_1, stom_xml_hm_zsl_sl_date_2, stom_xml_hm_zsl_sl_ds1, 
                                 stom_xml_hm_zsl_sl_c_zab, stom_xml_hm_zsl_sl_prvs, stom_xml_hm_zsl_sl_iddokt, 
                                 stom_xml_hm_zsl_sl_idcase, stom_xml_hm_zsl_sl_usl_count) 
                     VALUES ");
        $queryUSL = ("INSERT INTO stom_xml_hm_zsl_sl_usl (stom_xml_hm_zsl_sl_usl_idserv, stom_xml_hm_zsl_sl_usl_podr, 
                                  stom_xml_hm_zsl_sl_usl_profil, stom_xml_hm_zsl_sl_usl_date_in, stom_xml_hm_zsl_sl_usl_date_out, 
                                  stom_xml_hm_zsl_sl_usl_ds, stom_xml_hm_zsl_sl_usl_code_usl, 
                                  stom_xml_hm_zsl_sl_usl_sl_id) 
                      VALUES ");
        foreach ($hmZap AS $zap){
           $queryZSL .= (" ('{$zap['Z_SL'][0]['IDCASE']}', '{$zap['Z_SL'][0]['USL_OK']}', '{$zap['Z_SL'][0]['VIDPOM']}', 
                             '{$zap['Z_SL'][0]['FOR_POM']}', {$zap['Z_SL'][0]['DATE_Z_1']}, {$zap['Z_SL'][0]['DATE_Z_2']},
                             '{$zap['Z_SL'][0]['RSLT']}', '{$zap['Z_SL'][0]['ISHOD']}', '{$zap['Z_SL'][0]['IDSP']}',
                             '{$zap['Z_SL'][0]['OPLATA']}', '{$zap['Z_SL'][0]['ID_PAC']}'),");
           $sl = $zap['Z_SL'][0]['SL'];
           $querySL .= (" ('{$sl['SL_ID']}', '{$sl['PROFIL']}', '{$sl['DET']}', '{$sl['P_CEL']}', '{$sl['NHISTORY']}',
                            {$sl['DATE_1']}, {$sl['DATE_2']}, '{$sl['DS1']}', '{$sl['C_ZAB']}', '{$sl['PRVS']}', 
                            '{$sl['IDDOKT']}', '{$sl['IDCASE']}', {$sl['USL_COUNT']}),");
               foreach ($sl['USL'] AS $usl){
                   $queryUSL .= ("('{$usl['IDSERV']}', '{$usl['PODR']}', '{$usl['PROFIL']}', {$usl['DATE_IN']}, 
                   {$usl['DATE_OUT']}, '{$usl['DS']}', '{$usl['CODE_USL']}', '{$usl['SL_ID']}'),");
               }
        }
        $queryZSL = substr($queryZSL,0,-1);
        $querySL = substr($querySL,0,-1);
        $queryUSL = substr($queryUSL,0,-1);
        $stmt = $this->pdo->prepare($queryPacient);
        $stmt->execute();
        $stmt = $this->pdo->prepare($queryZSL);
        $stmt->execute();
        $stmt = $this->pdo->prepare($querySL);
        $stmt->execute();
        $stmt = $this->pdo->prepare($queryUSL);
        $stmt->execute();
        return true;
    }

    public function upload(array $registry){
        $result = [];
        /*if($this->lmUpload($registry['LM'])){
            $result['lmMessage'] = 'LM залит';
        }*/
        $this->pmUpload($registry['PM']);
        $this->hmUpload($registry['HM']);
        return $result;
    }

}