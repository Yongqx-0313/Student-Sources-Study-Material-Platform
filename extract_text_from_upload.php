<?php
// extract_text_from_upload.php
// POST a file under "file" and get JSON: { ok:true, text:"...", title: "optional" }

header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory as XlsIOFactory;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;

try {
  if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["ok" => false, "error" => "No file uploaded"]); exit;
  }

  $tmpPath  = $_FILES['file']['tmp_name'];
  $origName = $_FILES['file']['name'];
  $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

  // Optional: validate size/type if you want
  $text = '';

  if ($ext === 'pdf') {
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($tmpPath);
    $text = $pdf->getText();

  } elseif ($ext === 'docx' || $ext === 'doc') {
    $phpWord = WordIOFactory::load($tmpPath);
    $getText = function($el) use (&$getText) {
      if (method_exists($el, 'getText')) return $el->getText();
      if (method_exists($el, 'getElements')) {
        $s=''; foreach ($el->getElements() as $child) $s .= $getText($child);
        return $s;
      }
      return '';
    };
    foreach ($phpWord->getSections() as $section) {
      foreach ($section->getElements() as $el) {
        $text .= $getText($el) . "\n";
      }
    }

  }  else {
    // fallback (txt/md)
    $text = @file_get_contents($tmpPath);
  }

  $text = trim((string)$text);
  if ($text === '') { echo json_encode(["ok"=>false,"error"=>"Couldn’t extract text"]); exit; }

  $text = mb_substr($text, 0, 12000); // clamp
  echo json_encode(["ok"=>true, "title"=>$origName, "text"=>$text]);

} catch (Throwable $e) {
  echo json_encode(["ok"=>false,"error"=>$e->getMessage()]);
}
