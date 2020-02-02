<?php

if (!function_exists('dateFormat')) {
    /**
     * Formata uma data para o formato de SQL ou para o formato normal, 
     * o segundo parâmetro define se a data vai vir completa (com o tempo)
     *
     * @param [string] $dt
     * @param [boolean] $isFull
     * @return [string] date
     */
    function dateFormat($dt, $isFull = true)
    {
        $auxDt = date('d/m/Y');
        if (strpos($dt, '-') !== false) {
            $auxDt = explode(' ', $dt);
            $strDt = implode('/', array_reverse(explode('-', $auxDt[0]))) . ($isFull && sizeof($auxDt) > 1 && isset($auxDt[1]) ? ' ' . $auxDt[1] : '');
        } else {
            $auxDt = explode(' ', $dt);
            $strDt = implode('-', array_reverse(explode('/', $auxDt[0]))) . ($isFull && sizeof($auxDt) > 1 && isset($auxDt[1]) ? ' ' . $auxDt[1] : '');
        }
        return $strDt;
    }
}

if(!function_exists('limitaTexto')) {
    /**
     * Limita texto
     * Se o texto for maior que o limite, ele corta o texto e adiciona 3 pontinhos. 
     *
     * @param string $var
     * @param integer $limite
     * @return string
     */
    function limitaTexto( $var, $limite )
    {	
        if ( strlen($var) > $limite ) {		
            $var = substr($var, 0, $limite);		
            $var = trim($var) . "...";	
        }
        
        return $var;
    }
}


if(!function_exists('formatNumToMysql')) {
    /**
     * Formata números reais para o formato mysql
     * 
     * @link https://pt.stackoverflow.com/questions/349990/formatar-valor-do-real-br-para-decimal10-2-do-mysql
     * 
     * @return float
     *
     */
    function formatNumToMysql($real, $casasDecimais = 2) 
    {

        // Se já estiver no formato USD, retorna como float e formatado
        if(preg_match('/^\d+\.{1}\d+$/', $real)) {
            return (float) number_format($real, $casasDecimais, '.', '');
        }
        // Tira tudo que não for número, ponto ou vírgula
        $real = preg_replace('/[^\d\.\,]+/', '', $real);
        // Tira o ponto
        $decimal = str_replace('.', '', $real);
        // Troca a vírgula por ponto
        $decimal = str_replace(',', '.', $decimal);
        
        return (float) number_format($decimal, $casasDecimais, '.', '');
        
        // var_dump(formatNumToMysql('150.99', 2)); // float(150.99)
        // var_dump(formatNumToMysql('10.123456789', 3)); // float(10.123)
        // var_dump(formatNumToMysql('R$ 10,99', 2)); // float(10.99)
        // var_dump(formatNumToMysql('89,999', 3)); // float(89.999)
        // var_dump(formatNumToMysql('1.089,90')); // float(1089.9)
        // var_dump(formatNumToMysql('1.089,99')); // float(1089.99)
    }
}


if(!function_exists('formatMessage')) {
    /**
     * Format message response
     * 
     * @param number $code
     * @param string $message
     * @return array
     *
     */
    function formatMessage($code, $message, $titleMessage = 'message')
    {
        return ['code' => $code, $titleMessage => $message];
    }
}
