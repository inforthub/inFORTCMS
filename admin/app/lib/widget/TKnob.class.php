<?php
/**
 * TKnob
 *
 * @version    1.0
 * @package    widget
 * @author     inFORT
 * @author     André Ricardo Fort <andre [at] infort.eti.br>
 * @license    http://www.adianti.com.br/framework-license
 */
class TKnob extends TElement
{
    private $min;
    private $max;
    private $thickness;
    private $angleArc;
    private $angleOffset;
    private $width;
    private $height;
    private $color;
    private $label;
    private $value;
    private $readonly;
    private $tron;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct('div');
        //self::{'class'} = 'col-xs-6 col-md-3 text-center';

        $this->color       = '#3c8dbc';
        $this->width       = '90';
        $this->height      = '90';
        
        $this->min         = '0';
        $this->max         = '100';
        $this->thickness   = null;
        $this->angleArc    = null;
        $this->angleOffset = null;
        $this->readonly    = true;
        $this->tron        = false;
        
        $this->label       = null;
        $this->value       = null;
    }
    
    /**
     * Seta a cor
     */
    public function setColor ($value)
    {
        $this->color = $value;
    }
    
    /**
     * Seta a largura e a altura
     */
    public function setWidthHeight ($width, $height)
    {
        $this->width  = $width ? $width : '90';
        $this->height = $height ? $height : '90';
    }
    
    /**
     * Seta o valor mínimo e máximo
     */
    public function setMinMax ($min, $max)
    {
        $this->min = $min ? $min : '0';
        $this->max = $max ? $max : '100';
    }
    
    /**
     * Seta o thickness (expessura)
     */
    public function setThickness ($value)
    {
        $this->thickness = $value;
    }
    
    /**
     * Seta a angulação do arco
     */
    public function setArc ($angle='250', $offset='-125')
    {
        $this->angleArc    = $angle;
        $this->angleOffset = $offset;
    }
    
    /**
     * Seta a permissão para edição
     * @param    $value boolean
     */
    public function setReadOnly ($value=true)
    {
        $this->readonly = $value;
    }
    
    /**
     * Ativa o estilo Tron
     * @param    $value boolean
     */
    public function setTron ($value=true)
    {
        $this->tron = $value;
    }
    
    /**
     * Seta um label para o Knob
     */
    public function setLabel ($value)
    {
        $this->label = $value;
    }
    
    /**
     * Seta o valor
     */
    public function setValue ($value)
    {
        $this->value = $value;
    }
    
    /**
     * Método rápido de criação de um Knob
     * @param    $value - string
     * @param    $label - string
     * @param    $param - array
     */
    public static function put($value, $label=null, $param=null)
    {
        $knob = new TKnob;
        $knob->class = 'col-xs-6 col-md-3 text-center';
        
        $knob->setValue($value);

        if (isset($label)) $knob->setLabel($label);
        if (isset($param['color'])) $knob->setColor($param['color']);
        if (isset($param['width']) OR isset($param['height'])) $knob->setWidthHeight($param['width'],$param['height']);
        if (isset($param['max']) OR isset($param['min'])) $knob->setMinMax($param['min'],$param['max']);
        if (isset($param['thickness'])) $knob->setThickness($param['thickness']);
        if (isset($param['angleArc']) && isset($param['angleOffset'])) $knob->setArc($param['angleArc'],$param['angleOffset']);
        if (isset($param['readonly'])) $knob->setReadOnly($param['readonly']);
        if (isset($param['tron'])) $knob->setTron($param['tron']);
        
        return $knob;
    }
    
    /**
     * Shows the knob
     */   
    public function show()
    {
        $input = '<input type="text" class="knob" ';
        
        if ($this->tron) $input .= 'data-skin="tron" ';
        if ($this->thickness) $input .= 'data-thickness="'.$this->thickness.'" ';
        if ($this->angleArc && $this->angleOffset) $input .= 'data-angleArc="'.$this->angleArc.'" data-angleOffset="'.$this->angleOffset.'" ';
        
        $input .= 'data-min="'.$this->min.'" data-max="'.$this->max.'" ';
        $input .= 'value="'.$this->value.'" data-width="'.$this->width.'" data-height="'.$this->height.'" data-fgColor="'.$this->color.'"';
        
        if ($this->readonly) $input .= ' data-readonly="'.$this->readonly.'"';
        
        $input .= '>';

        parent::add($input);
        
        if ($this->label)
            parent::add('<div class="knob-label">'.$this->label.'</div>');
            
        // exibindo
        parent::show();
    }
    
    /**
     * Retorna o script necessário para iniciar o Knob
     */
    public static function getScript()
    {
        $script = '
            /* jQueryKnob */
            $(".knob").knob({
                /*change : function (value) {
                //console.log("change : " + value);
                 },
                release : function (value) {
                console.log("release : " + value);
                 },
                cancel : function () {
                console.log("cancel : " + this.value);
                },*/
                draw : function () {
                    
                    if (this.$.data("skin") == "tron") {
                        var a = this.angle(this.cv)
                          , sa = this.startAngle
                          , sat = this.startAngle
                          , ea
                          , eat = sat + a
                          , r = true;
            
                        this.g.lineWidth = this.lineWidth;
            
                        this.o.cursor
                        && (sat = eat - 0.3)
                        && (eat = eat + 0.3);
            
                        if (this.o.displayPrevious) {
                            ea = this.startAngle + this.angle(this.value);
                            this.o.cursor
                            && (sa = ea - 0.3)
                            && (ea = ea + 0.3);
                            this.g.beginPath();
                            this.g.strokeStyle = this.previousColor;
                            this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
                            this.g.stroke();
                        }
            
                        this.g.beginPath();
                        this.g.strokeStyle = r ? this.o.fgColor : this.fgColor;
                        this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
                        this.g.stroke();
                        
                        this.g.lineWidth = 2;
                        this.g.beginPath();
                        this.g.strokeStyle = this.o.fgColor;
                        this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
                        this.g.stroke();
            
                        return false;
                    }
                }
            });
            /* END JQUERY KNOB */
        ';
        
        return $script;
    }
    
    
}
