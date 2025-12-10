<?php

namespace Application\InvoiceRegisterAnalyzer\Models;

use Engine\Database\IConnector;
use Engine\DTO\StructuredResponse;

class IncorrectTeethFinder
{
    private $_withoutCode = [
        '19ALL.0', '19B00.2', '19B37.0', '19D10.1', '19D10.10', '19K03.0', '19K03.6', '19K05.0', '19L05.1', '19K05.3',
        '19L05.4', '19K07.5', '19K07.6', '19K11.2', '19K12.0', '19K12.1', '19K13.0', '19K13.2', '19K14.0', '19K14.1',
        '19K14.6', '19L43.3', '19M12.8', '19S00.5', '19S00.7', '19S01.4', '19S01.5', '19S02.6', '19Z01.2', '19Z01.21',
        '19Z01.22', '19Z01.23', '19K05.4'
    ];

    private $_withCode = [
        '19K02.1', '19K02.2', '19K04.01', '19K04.02', '19K04.03', '19K04.0', '19K04.04', '19K04.05', '19K04.06',
        '19K04.4', '19K04.41', '19K04.42', '19K04.43', '19K04.44', '19K04.45', '19K04.46', '19K04.5', '19K04.51',
        '19K04.52', '19K04.53', '19K04.54', '19K04.55', '19K04.56', '19K04.8', '19K05.2', '19K05.31', '19K05.32',
        '19K05.41', '19K06.8', '19K08.3', '19K10.2', '19K10.3'
    ];

    private $_simultaneousCode = [
        '19K02.1', '19K02.2', '19K04.01', '19K04.02', '19K04.03', '19K04.04', '19K04.05', '19K04.06', '19K04.4',
        '19K04.41', '19K04.42', '19K04.43', '19K04.44', '19K04.45', '19K04.46', '19K04.5', '19K04.51', '19K04.52',
        '19K04.53', '19K04.54', '19K04.55', '19K04.56', '19K04.8', '19K05.31', '19K05.41', '19K10.2', '19K10.3'
    ];
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    /**
     * Найти те случаи в которых более 1 услуги
     * @return StructuredResponse
     */
    public function findMultipleServices() : StructuredResponse{
        $query = ("SELECT stom_xml_pm_sl_stom_sl_id FROM stom_xml_pm_sl_stom
                   WHERE stom_xml_pm_sl_stom_idstom > 1");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $response = new StructuredResponse();
        if (!empty($result)){
            $IDs = '';
            foreach ($result AS $single){
                $IDs .= "'".$single['stom_xml_pm_sl_stom_sl_id']."', ";
            }
            $response->body = substr($IDs,0,-2);
            $response->message = 'Данные загружены';
        }
        else{
            $response->message = 'Данные из XML не загружены. Таблица не содержат данных';
        }
        return $response;
    }

    /**
     * Выбираю данные содержащие названия зубов и диагнозы, для тех случаев у которых более 1 услуги
     * @param StructuredResponse $response
     * @return StructuredResponse
     */
    public function findTeethCodesAndDiagnosis(StructuredResponse $response) : StructuredResponse{
        if ($response->body !== []){
            $query = ("SELECT * FROM stom_xml_pm_sl_stom WHERE stom_xml_pm_sl_stom_sl_id IN ($response->body)");
            //$query = ("SELECT * FROM stom_xml_pm_sl_stom
                   //WHERE stom_xml_pm_sl_stom_idstom > 1"); Посмотреть этот путь!
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $newResult = [];
            while ($result = $stmt->fetch()){
                $newResult[$result['stom_xml_pm_sl_stom_sl_id']][$result['stom_xml_pm_sl_stom_idstom']]['stom_xml_pm_sl_stom_code_usl'] = $result['stom_xml_pm_sl_stom_code_usl'];
                $newResult[$result['stom_xml_pm_sl_stom_sl_id']][$result['stom_xml_pm_sl_stom_idstom']]['stom_xml_pm_sl_stom_zub']  = $result['stom_xml_pm_sl_stom_zub'];
            }
            $response->body = $newResult;
            $response->message = 'Данные загружены';
        }else{
            $response->message = 'Данные из XML не загружены. Таблица "содержащая коды зубов и диагнозы" не содержат данных';
        }
        return $response;
    }

    /**
     * Отбираю ID случаев, где есть пересечения диагнозов на 1 зуб
     * @param StructuredResponse $response
     * @param $simultaneousCodes
     * @return StructuredResponse
     */
    public function findSimultaneousCasesIDs(StructuredResponse $response): StructuredResponse{
        if ($response->body !== []){
            $simultaneous = [];
            foreach ($response->body AS $id => $single){
                $zub = [];
                $diagnosis = [];
                foreach ($single as $key => $value) {
                    if (in_array($value['stom_xml_pm_sl_stom_code_usl'], $this->_simultaneousCode) AND $value['stom_xml_pm_sl_stom_zub'] !==''){
                        //$buffer[$key]['id'] = $key;
                        //$buffer[$key]['zub'] = $value['stom_xml_pm_sl_stom_zub'];
                        $zub[$key] = $value['stom_xml_pm_sl_stom_zub'];
                        $diagnosis[$key] = $value['stom_xml_pm_sl_stom_code_usl'];
                    }
                }
                /**
                 * В массив zub попадает название зуба типа НЛ6, только для тех зубов диагнозы которых совпадают с массивом
                 * $_simultaneousCode
                 * Массив может выглядеть так:
                 * $zub = [1 = НП6, 2 = НП7] или так $zub = [1 = НП6, 2 = НП6]
                 * Далее идет сравненение, если количество элементов массива $zub больше чем количество уникальных значений,
                 * т.е. array_unique() - удаляет дубликаты из $zub, тем самым обозначая что в оригинальном массиве $zub было
                 * два диагноза из $_simultaneousCode, которые были присвоены одному и тому же зубу, допустим НП6
                 * Тем самым если оригинальный массив больше, чем обработанный с учетом удаления дубликатов, значит в массив
                 * мы добавляем ID случая, в котором было такое совпадение
                 */
                if (count($zub) > count(array_unique($zub))){
                    $simultaneous[] = $id;
                }
            }
            if ($simultaneous !== []){
                $slIDs = '';
                foreach ($simultaneous as $single){
                    $slIDs .= "'".$single."'".', ';
                }
                $response->body = substr($slIDs,0,-2);
                $response->message = 'Данные загружены';
            }else{
                $response->message = 'В результате анализа не найдены ID случаев с пересечением двух диагнозов на 1 зубе';
                $response->body = [];
            }
        }
        return $response;
    }

    public function findIncorrectTeethCodeInclusion() : StructuredResponse{
        $query = ("SELECT * FROM stom_xml_pm_sl_stom
                   INNER JOIN stom_xml_hm_zsl ON stom_xml_hm_zsl_idcase = stom_xml_pm_sl_stom_sl_idcase
                   INNER JOIN stom_xml_lm ON stom_xml_lm__id_pac = stom_xml_hm_zsl_id_pac
                    ");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $response = new StructuredResponse();
        if ($result !== []){
            $incorrectTeethCodeInclusion = [];
            foreach ($result AS $single){
                if ($single['stom_xml_pm_sl_stom_zub'] !== ''){
                    $single['stom_xml_hm_zsl_date_z_1'] = date('d.m.Y', $single['stom_xml_hm_zsl_date_z_1']);
                    $single['stom_xml_hm_zsl_date_z_2'] = date('d.m.Y', $single['stom_xml_hm_zsl_date_z_2']);
                    if (in_array($single['stom_xml_pm_sl_stom_code_usl'], $this->_withoutCode)){
                        $incorrectTeethCodeInclusion[] = $single;
                        $response->body = $incorrectTeethCodeInclusion;
                    }
                }
            }
            if ($response->body !==[]){
                $response->message = 'Данные загружены';
            }
            else{
                $response->message = 'Некорректные включения кодов зубов для заданных диагнозов не найдены';
            }
        }else{
            $response->message = 'Данные из XML не загружены. Таблица не содержат данных';
        }
        return $response;
    }

    /**
     * @param $withCode
     * @return StructuredResponse
     */
    public function findRequiredTeethCode() : StructuredResponse{
        $query = ("SELECT * FROM stom_xml_pm_sl_stom
                   INNER JOIN stom_xml_hm_zsl ON stom_xml_hm_zsl_idcase = stom_xml_pm_sl_stom_sl_idcase
                   INNER JOIN stom_xml_lm ON stom_xml_lm__id_pac = stom_xml_hm_zsl_id_pac
                    ");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $response = new StructuredResponse();
        if ($result !== []){
            $requiredTeethCode = [];
            foreach ($result AS $single){
                $single['stom_xml_hm_zsl_date_z_1'] = date('d.m.Y', $single['stom_xml_hm_zsl_date_z_1']);
                $single['stom_xml_hm_zsl_date_z_2'] = date('d.m.Y', $single['stom_xml_hm_zsl_date_z_2']);
                if ($single['stom_xml_pm_sl_stom_zub'] === ''){
                    if (in_array($single['stom_xml_pm_sl_stom_code_usl'], $this->_withCode)){
                        $requiredTeethCode[] = $single;
                        $response->body = $requiredTeethCode;
                    }
                }
            }
            $response->message = 'Данные загружены';
        }else{
            $response->message = 'Данные из XML не загружены. Таблица не содержат данных';
        }
        return $response;
    }

    /**
     * @param StructuredResponse $response
     * @return StructuredResponse
     */
    public function findSimultaneousTeethInclusion(StructuredResponse $response) : StructuredResponse{
        if ($response->body !== []){
            $query = ("SELECT * FROM stom_xml_lm
                   INNER JOIN stom_xml_hm_zsl ON stom_xml_hm_zsl_id_pac = stom_xml_lm__id_pac
                   INNER JOIN stom_xml_pm_sl_stom ON stom_xml_pm_sl_stom_sl_idcase = stom_xml_hm_zsl_idcase
                   WHERE stom_xml_pm_sl_stom_sl_id IN ($response->body)");
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll();
            //Форматирую даты
            $simultaneous = [];
            foreach ($result AS $single){
                $single['stom_xml_hm_zsl_date_z_1'] = date('d.m.Y', $single['stom_xml_hm_zsl_date_z_1']);
                $single['stom_xml_hm_zsl_date_z_2'] = date('d.m.Y', $single['stom_xml_hm_zsl_date_z_2']);
                $simultaneous[] = $single;
            }
            $response->message = 'Данные загружены';
            $response->body = $simultaneous;
        }
        return $response;

    }

}