<?php
// extract_text.php
// returns JSON: { "ok": true, "title": "...", "text": "..." } or { "ok": false, "error": "..." }

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory as XlsIOFactory;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;

$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
  echo json_encode(["ok" => false, "error" => "DB connection failed"]); exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT pdf_file, title FROM resources WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($fileRelPath, $title);
$stmt->fetch();
$stmt->close();
$conn->close();

if (!$fileRelPath) { echo json_encode(["ok" => false, "error" => "File not found"]); exit; }

$filePath = __DIR__ . '/' . $fileRelPath;
if (!file_exists($filePath)) { echo json_encode(["ok" => false, "error" => "File missing on disk"]); exit; }

$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

try {
  $text = '';

  if ($ext === 'pdf') {
    // ---- PDF ----
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($filePath);
    $text = $pdf->getText();

  } elseif ($ext === 'docx' || $ext === 'doc') {
    // ---- DOC/DOCX ----
    $phpWord = WordIOFactory::load($filePath);

    // helper to get text out of PhpWord elements
    $getText = function($el) use (&$getText) {
      // plain text elements
      if (method_exists($el, 'getText')) {
        return $el->getText();
      }
      // TextRun / Container
      if (method_exists($el, 'getElements')) {
        $s = '';
        foreach ($el->getElements() as $child) {
          $s .= $getText($child);
        }
        return $s;
      }
      return '';
    };

    foreach ($phpWord->getSections() as $section) {
      foreach ($section->getElements() as $el) {
        $text .= $getText($el) . "\n";
      }
    }

  } elseif ($ext === 'csv') {
    // ---- CSV ----
    if (($fh = fopen($filePath, 'r')) !== false) {
      while (($row = fgetcsv($fh)) !== false) {
        $text .= implode(' ', array_map('strval', $row)) . "\n";
      }
      fclose($fh);
    }

  } else {
    // ---- Fallback (txt, md, etc.) ----
    $text = file_get_contents($filePath);
  }

  // basic sanity
  $text = trim($text);
  if ($text === '') {
    echo json_encode(["ok" => false, "error" => "Couldn't extract text from file"]); exit;
  }

  // Optional: clamp overly long input to ~12k chars
  $text = mb_substr($text, 0, 12000);

  echo json_encode(["ok" => true, "title" => $title, "text" => $text]);

} catch (Throwable $e) {
  echo json_encode(["ok" => false, "error" => $e->getMessage()]);
}
