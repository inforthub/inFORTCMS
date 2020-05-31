<?php
/**
 * TSlim Container
 * Copyright (c) 2006-2010 Nataniel Rabaioli
 * @author  Nataniel Rabaioli
 * @version 2.0, 2007-08-01
 */
class TSlim extends TField implements AdiantiWidgetInterface
{
    protected $value;
    public $container;
    
    protected $scripts;
    
    /**
     * Class Constructor
     */
    public function __construct($name)
    {
        parent::__construct($name);
        //$this->id = 'slim_' . uniqid();
        
        $this->tag->type = 'file';
        
        $this->scripts = null;
        
        $this->container = new TElement('div');
        $this->container->class = 'slim';
        $this->container->style = 'width:100%;height:auto;border:2px solid lightgray';
        
        $this->setDataProperties(['size'=>'640,640','label'=>'Upload imagem','button-confirm-label'=>'Confirmar',
                                  'button-confirm-title'=>'Confirmar','button-cancel-label'=>'Cancelar',
                                  'button-cancel-title'=>'Cancelar','button-edit-label'=>'',
                                  'button-edit-title'=>'Editar','button-remove-label'=>'',
                                  'button-remove-title'=>'Remover','button-rotate-label'=>'Girar',
                                  'button-rotate-title'=>'Girar','button-download-label'=>'',]);
                                  
    }
    
    
    public function setDataProperties($props)
    {
        foreach ($props as $prop => $val)
        {
            $this->container->{"data-{$prop}"} = $val;
        }
    }
    
    /**
     * Seta o estilo do container no form
     * Ex: 'width:100%;height:auto;border:2px solid lightgray'
     */
    public function setContainerStyle($style)
    {
        $this->container->style = $style;
    }
    
    /**
     * Método para exibir um texto na foto
     */
    public function setWatermark($mensagem)
    {
        if (!empty($mensagem))
        {
            $this->setDataProperties(['will-transform'=>'addWatermark']);
            $this->scripts[] = "
                function addWatermark(data, ready) {

                    var ctx = data.output.image.getContext('2d');
                    var size = data.output.width / 40;
                    ctx.font = size + 'px sans-serif';
                    var x = data.output.width * .5;
                    var y = data.output.height * .93;
                    var text = ctx.measureText('".$mensagem."');
                    var w = text.width * 1.15;
                    var h = size * 1.75;
                
                    ctx.fillStyle = 'rgba(0,0,0,.75)';
                    ctx.fillRect(
                        x - (w * .5),
                        y - (h * .5),
                        w, h
                    );
                    ctx.fillStyle = 'rgba(255,255,255,.9)';
                    ctx.fillText(
                        '".$mensagem."',
                        x - (text.width * .5),
                        y + (size * .35)
                    );

                    ready(data);
                }";
        }
    }
    
    /**
     * Método para exibir uma imagem como marca d'água na foto
     */
    public function setImageWatermark($url)
    {
        if (!empty($url))
        {
            $this->setDataProperties(['will-transform'=>'addImageWatermark']);
            $this->scripts[] = "
                function addImageWatermark(data, ready) {

                    var watermark = new Image();
                    watermark.onload = function() {
                        var offset = data.output.width * .05;
                        var width = data.output.width * .25;
                        var height = width * (this.naturalHeight / this.naturalWidth);
                        var ctx = data.output.image.getContext('2d');
                        ctx.globalAlpha = .75;
                        ctx.globalCompositeOperation = 'multiply';
                        ctx.drawImage(watermark, offset, offset, width, height);
                        ready(data);
                    };
                    watermark.src = '".$url."';
                }";
        }
    }
    
    /**
     * Shows the widget at the screen
     */
    public function show()
    {
        $this->container->add($this->tag);
        
        if ($this->value)
            $this->container->add(new TImage($this->value));
        
        $js = TScript::create('',false);
        $js->src = 'app/lib/include/slim/slim.kickstart.min.js';
        $this->container->add($js);
        
        $this->container->show();
        
        if (!empty($this->scripts))
        {
            foreach ($this->scripts as $val)
            {
                TScript::create($val);
            }
        }
    }
}
