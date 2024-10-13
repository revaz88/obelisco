<?php

namespace modules\sga\estatisticas;

/**
 * Grafico.
 *
 * 
 */
class Grafico extends Relatorio
{
    private $legendas = array();

    public function __construct($titulo, $tipo, $opcoes = '')
    {
        parent::__construct($titulo, $tipo, $opcoes);
    }

    public function getLegendas()
    {
        return $this->legendas;
    }

    public function setLegendas($legendas)
    {
        $this->legendas = $legendas;
    }

    public function jsonSerialize()
    {
        return array(
            'tipo' => $this->arquivo,
            'titulo' => $this->titulo,
            'dados' => $this->dados,
            'legendas' => $this->legendas,
        );
    }
}
