<?php

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\TablePosition;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\TemplateProcessor;

class PhpWord
{
    private $Service;
    private $file;
    private $TemplateProcessor;

    public function __construct()
    {
        $this->Service = new \PhpOffice\PhpWord\PhpWord();
    }

    public function getService()
    {
        return $this->Service;
    }

    public function setLoadTemplate($file)
    {
        $this->file = $file;
        $this->TemplateProcessor = new TemplateProcessor($file);
    }

    public function getTemplate()
    {
        return $this->TemplateProcessor;
    }

    public function save()
    {
        $objWriter = IOFactory::createWriter($this->getService(), 'Word2007');
        $objWriter->save($this->file);
    }

    public function setFile($file)
    {
        $this->file = $file;
    }
}