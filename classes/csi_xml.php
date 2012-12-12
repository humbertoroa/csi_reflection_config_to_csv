<?php

class Csi_xml {

    static function getParameterObject($xml){

        $result = array();

        foreach($xml->Parameter as $xmlParameters){

            $p = new StdClass();
            $p->id = (string) $xmlParameters['Id'];
            $p->type =  (string) $xmlParameters['Type'];
            $p->value =  (string) $xmlParameters['Value'];
            $result[$p->id] = $p;
        }

        return $result;

    }


}