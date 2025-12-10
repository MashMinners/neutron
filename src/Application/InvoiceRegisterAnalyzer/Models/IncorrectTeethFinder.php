<?php

namespace Application\InvoiceRegisterAnalyzer\Models;

use Engine\Database\IConnector;
use Engine\DTO\StructuredResponse;

class IncorrectTeethFinder
{
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
    public function findSimultaneousCasesIDs(StructuredResponse $response, $simultaneousCodes): StructuredResponse{
        if ($response->body !== []){
            $simultaneous = [];
            foreach ($response->body AS $id => $single){
                $zub = [];
                $diagnosis = [];
                foreach ($single as $key => $value) {
                    if (in_array($value['stom_xml_pm_sl_stom_code_usl'], $simultaneousCodes) AND $value['stom_xml_pm_sl_stom_zub'] !==''){
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

    public function findIncorrectTeethCodeInclusion(array $withoutCode) : StructuredResponse{
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
                    if (in_array($single['stom_xml_pm_sl_stom_code_usl'], $withoutCode)){
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
    public function findRequiredTeethCode(array $withCode) : StructuredResponse{
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
                    if (in_array($single['stom_xml_pm_sl_stom_code_usl'], $withCode)){
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