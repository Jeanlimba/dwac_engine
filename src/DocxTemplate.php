<?php

class DocxTemplate {
    private $templatePath;
    private $tempFile;

    public function __construct($templatePath) {
        if (!file_exists($templatePath)) {
            throw new Exception("Template file not found: " . $templatePath);
        }
        $this->templatePath = $templatePath;
    }

    public function replace($data) {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'docx') . '.docx';
        copy($this->templatePath, $this->tempFile);

        $zip = new ZipArchive();
        if ($zip->open($this->tempFile) === TRUE) {
            $documentXml = $zip->getFromName('word/document.xml');

            // Clean split placeholders like ${<...>} or {{<...>}}
            $documentXml = preg_replace_callback('/\$\{([^\}]*)\}/U', function($match) {
                return '${' . strip_tags($match[1]) . '}';
            }, $documentXml);
            
            $documentXml = preg_replace_callback('/\{\{([^\}]*)\}\}/U', function($match) {
                return '{{' . strip_tags($match[1]) . '}}';
            }, $documentXml);

            foreach ($data as $key => $value) {
                $val = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                $documentXml = str_replace('${' . $key . '}', $val, $documentXml);
                $documentXml = str_replace('{{' . $key . '}}', $val, $documentXml);
            }

            $zip->addFromString('word/document.xml', $documentXml);
            if (!$zip->close()) {
                throw new Exception("Could not save docx file.");
            }
            return $this->tempFile;
        } else {
            throw new Exception("Could not open docx file.");
        }
    }

    public function download($filename) {
        if (file_exists($this->tempFile)) {
            if (ob_get_length()) ob_clean();
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($this->tempFile));
            readfile($this->tempFile);
            unlink($this->tempFile);
            exit;
        }
    }
}
