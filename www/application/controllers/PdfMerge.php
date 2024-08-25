<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\StreamReader;

require_once('application/libraries/pdf/src/autoload.php');
// require_once('application/libraries/mpdf/vendor/autoload.php');
// require_once('application/libraries/pdfkt/vendor/autoload.php');

use mikehaertl\pdftk\Pdf;
class PdfMerge extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper('form');

		$this->load->library('fpdf/fpdf.php');
       

    }

    public function install_pdftk() {
        // Path to the uploaded installer file
        // $installerPath = 'pdftk_free-2.02-win-setup.exe';
        // Full path to the installer file based on your setup
        $installerPath = 'gs10031w64.exe';

        // Command to install PDFtk directly
        $installCommand = "\"$installerPath\" /S";

        // Execute the command
        $output = shell_exec($installCommand);

        // Check if the installation was successful
        if ($output === null) {
            // echo "PDFtk installed successfully.";
            return true;
        } else {
            return false;
        }
    }

    public function index() {
        $pdftkPath = '"C:\Program Files\gs\gs10.03.1\bin\"';
        $command = "if not exist $pdftkPath echo not exists!";
        $output = shell_exec($command);

        // print_r(strpos($output, 'not exists!'));
        // print_r($output != "");die;
        if ($output != "") {
            // PDFtk is not installed, so install it
            $this->install_pdftk();
        
        } else {
            // echo "PDFtk is already installed at: " . $output;
        }
        $this->load->view('pdf_merge');
    }

    public function merge_pdfs() {
        $mainFolder = $this->input->post('directory');
        $outputFolder = $this->input->post('outputFolder');
        $pageOrientation = $this->input->post('pageOrientation');
        $mergeOption = $this->input->post('mergeOption');

        if (!is_dir($mainFolder)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid directory']);
            return;
        }

        $subFolders = glob($mainFolder . '/*', GLOB_ONLYDIR);
        $totalFiles = count($subFolders);
        $processed = 0;

        // Send initial response
        header('Content-Type: application/json');
        if (ob_get_level() === 0) {
            ob_start();
        }
        echo json_encode(['status' => 'processing', 'progress' => 10]) . "\n";
        ob_flush();
        flush();

        foreach ($subFolders as $subFolder) {
            $this->processFolder($subFolder, $outputFolder, $pageOrientation, $mergeOption);
            $processed++;
            $percentage = round(($processed / $totalFiles) * 100);
            // echo json_encode(['status' => 'processing', 'progress' => $percentage]);
            // flush();
            // ob_flush();

            echo json_encode(['status' => 'processing', 'progress' => $percentage]) . "\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }

        // Send final completion message
        echo json_encode(['status' => 'completed']) . "\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    private function preprocessPdf($pdfFile) {
        $preprocessedFile = sys_get_temp_dir() . '/' . uniqid() . '_preprocessed.pdf';

        $command = '"C:\Program Files\gs\gs10.03.1\bin\gswin64" -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -sOutputFile=' . escapeshellarg($preprocessedFile) . ' ' . escapeshellarg($pdfFile);

        // print_r($command);die;
        
        exec($command, $output, $returnVar);
        
        
        if ($returnVar !== 0) {
            throw new Exception("Error in preprocessing PDF: " . implode("\n", $output));
        }

        return $preprocessedFile;
    }



    // private function mergeSideBySide($pdfFile, $outputFile, $pageOrientation) {

    //     // $pdfFile = $this->preprocessPdf($pdfFile); // Pre-process the PDF


    //     // try {
	// 	// Preprocess PDF (use Ghostscript or any other method)
	// 	// $preprocessedPdf = $this->preprocessPdf($pdfFile);

	// 	// Convert PDF to images
	// 	// $pdfFile = $this->preprocessPdf($pdfFile); // Pre-process the PDF

    //     // print_r($pdfFile);die;
    //     $pdf = new Fpdi();
    //     $pageCount = $pdf->setSourceFile($pdfFile);

    //     $pdf->AddPage($pageOrientation);
    //     for ($i = 1; $i <= $pageCount; $i += 2) {
    //         $tplIdx1 = $pdf->importPage($i);
    //         $tplIdx2 = ($i + 1 <= $pageCount) ? $pdf->importPage($i + 1) : null;

    //         $size = $pdf->getTemplateSize($tplIdx1);

    //         // First Page (left side)
    //         $pdf->useTemplate($tplIdx1, 0, 0, $size['width'], $size['height']);

    //         // Second Page (right side), if exists
    //         if ($tplIdx2) {
    //             $pdf->useTemplate($tplIdx2, $size['width'], 0, $size['width'], $size['height']);
    //         }

    //         if ($i + 1 < $pageCount) {
    //             $pdf->AddPage($pageOrientation);
    //         }
    //     }

    //     $pdf->Output($outputFile, 'F');
    //     unlink($pdfFile); // Delete the preprocessed file after merging
	// 	// } catch (Exception $e) {
	// 	// 	// Handle exceptions gracefully by logging or reporting the error
	// 	// 	error_log("Error merging PDF: " . $e->getMessage());
	// 	// 	// Optionally, you can return an error message or flag
	// 	// }
    // }

    // private function preprocessPdf($pdfFile) {

    //     $pdf = $this->pdfparser->parseFile($pdfFile);


    //     // Extract text or objects
    //     // $text = $pdf->getText();
    //     $pages = $pdf->getPages();

    //     print_r($pdf);die;


    //     // Create a new PDF document using TCPDF or any other PDF library
    //     $tcpdf = new TCPDF();

    //     // Set PDF metadata
    //     $tcpdf->SetCreator(PDF_CREATOR);
    //     $tcpdf->SetAuthor('Author');
    //     $tcpdf->SetTitle('Uncompressed PDF');
    //     $tcpdf->SetSubject('PDF Preprocessing');
    //     $tcpdf->SetKeywords('TCPDF, PDF, uncompress, preprocess');

    //     // Add a page
    //     $tcpdf->AddPage();

    //     // Add the extracted text to the new PDF document
    //     $tcpdf->Write(0, $text);

    //     // Set PDF version to 1.6 (or as needed)
    //     $tcpdf->SetPDFVersion('1.6');

    //     // Save the new PDF
    //     $outputFile = sys_get_temp_dir() . '/' . uniqid() . '_preprocessed.pdf';
    //     $tcpdf->Output($outputFile, 'F');

    //     return $outputFile;

    //     // $preprocessedFile = sys_get_temp_dir() . '/' . uniqid() . '_preprocessed.pdf';
    
    //     // // Load the existing PDF
    //     // $pdf = new TCPDF();
    
    //     // // Set the PDF version to 1.6 (or any other version you require)
    //     // $pdf->SetPDFVersion('1.6');
    
    //     // // Import the pages from the existing PDF
    //     // $pageCount = $pdf->setSourceFile($pdfFile);
    
    //     // // Add each page to the new PDF document
    //     // for ($i = 1; $i <= $pageCount; $i++) {
    //     //     $tplIdx = $pdf->importPage($i);
    //     //     $pdf->AddPage();
    //     //     $pdf->useTemplate($tplIdx);
    //     // }
    
    //     // // Save the preprocessed file
    //     // $pdf->Output($preprocessedFile, 'F');
    
    //     // return $preprocessedFile;
    // }
    
    // private function preprocessPdf($pdfFile) {
    //     // Parse the PDF file
    //     $pdf = $this->pdfparser->parseFile($pdfFile);
    
    //     // Get all pages
    //     $pages = $pdf->getPages();
    
    //     // Create a new PDF document using TCPDF
    //     $tcpdf = new TCPDF();
    
    //     // Set PDF metadata
    //     $tcpdf->SetCreator(PDF_CREATOR);
    //     $tcpdf->SetAuthor('Author');
    //     $tcpdf->SetTitle('Uncompressed PDF');
    //     $tcpdf->SetSubject('PDF Preprocessing');
    //     $tcpdf->SetKeywords('TCPDF, PDF, uncompress, preprocess');
    
    //     // Set PDF version to 1.6 (or as needed)
    //     $tcpdf->SetPDFVersion('1.6');
    
    //     // Iterate through each page and extract images
    //     foreach ($pages as $page) {
    //         // Add a new page in TCPDF
    //         $tcpdf->AddPage();
    
    //         // Extract images
    //         $images = $pdf->getObjectsByType('XObject');
            
    //         foreach ($images as $image) {
    //             if ($image) {
    //                 // Retrieve the image data
    //                 $imageData = $image->getContent();
                    
    //                 // Save the image temporarily
    //                 $tmpImageFile = sys_get_temp_dir() . '/' . uniqid() . '.png';
    //                 file_put_contents($tmpImageFile, $imageData);
    
    //                 // Get image dimensions
    //                 $imageSize = getimagesize($tmpImageFile);
    //                 $imageWidth = $imageSize[0];
    //                 $imageHeight = $imageSize[1];
                    
    //                 // Get PDF page dimensions
    //                 $pageWidth = $tcpdf->GetPageWidth();
    //                 $pageHeight = $tcpdf->GetPageHeight();
    
    //                 // Scale the image to fit the page
    //                 $scaleWidth = $pageWidth / $imageWidth;
    //                 $scaleHeight = $pageHeight / $imageHeight;
    //                 $scale = min($scaleWidth, $scaleHeight);
    
    //                 // Calculate new image dimensions
    //                 $scaledWidth = $imageWidth * $scale;
    //                 $scaledHeight = $imageHeight * $scale;
    
    //                 // Center the image on the page
    //                 $xPos = ($pageWidth - $scaledWidth) / 2;
    //                 $yPos = ($pageHeight - $scaledHeight) / 2;
    
    //                 // Add image to TCPDF
    //                 $tcpdf->Image($tmpImageFile, $xPos, $yPos, $scaledWidth, $scaledHeight, '', '', '', false);
    
    //                 // Clean up the temporary image file
    //                 unlink($tmpImageFile);
    //             }
    //         }
    //     }
    
    //     // Save the new PDF
    //     $outputFile = sys_get_temp_dir() . '/' . uniqid() . '_preprocessed.pdf';
    //     $tcpdf->Output($outputFile, 'F');
        
    //     return $outputFile;
    // }
    
    // private function preprocessPdf($pdfFile) {

    //     print_r($pdfFile);
    //     // Parse the PDF file
    //     $pdf = $this->pdfparser->parseFile($pdfFile);
    
    //     // Get all pages
    //     $pages = $pdf->getPages();
    
    //     // Create a new PDF document using TCPDF
    //     $tcpdf = new TCPDF();
    
    //     // Set PDF metadata
    //     $tcpdf->SetCreator(PDF_CREATOR);
    //     $tcpdf->SetAuthor('Author');
    //     $tcpdf->SetTitle('Uncompressed PDF');
    //     $tcpdf->SetSubject('PDF Preprocessing');
    //     $tcpdf->SetKeywords('TCPDF, PDF, uncompress, preprocess');
    
    //     // Set PDF version to 1.6 (or as needed)
    //     $tcpdf->SetPDFVersion('1.6');
    
    //     $spaceBetweenPages = 20; // 20px space between the two pages

    //     $images = $pdf->getObjectsByType('XObject');

    //     // Iterate through each page and extract images
    //     foreach ($pages as $pageIndex => $page) {


    //         // print_r($page->getXObjects());die;
    //         // Add a new page in TCPDF
    
    //         // Extract images
            
    //         // if (empty($images)) {
    //         //     error_log("No images found on page " . ($pageIndex + 1));
    //         // }
    
    //         foreach ($page->getXObjects() as $key => $image) {
    //             if ($image) {

    //                 // Retrieve the image data
    //                 $imageData = $image->getContent();
    
    //                 // Check if image data is valid
    //                 if (empty($imageData)) {
    //                    echo "Image data is empty on page " . ($pageIndex + 1);
    //                     continue;
    //                 }
                    
    //                 // Save the image temporarily
    //                 $tmpImageFile = sys_get_temp_dir() . '/' . uniqid() . '.png';
    //                 file_put_contents($tmpImageFile, $imageData);
    
    //                 // Verify if the image file is created and is not empty
    //                 if (filesize($tmpImageFile) <= 0) {
    //                     echo "Image file is empty or not created on page " . ($pageIndex + 1);
    //                     continue;
    //                 }
    
    //                 // Get image dimensions
    //                 $imageSize = getimagesize($tmpImageFile);
    //                 if($imageSize){
    //                     $tcpdf->AddPage();

    //                     $imageWidth = $imageSize[0];
    //                     $imageHeight = $imageSize[1];
    
    //                     // Get PDF page dimensions
    //                     $pageWidth = $tcpdf->GetPageWidth();
    //                     $pageHeight = $tcpdf->GetPageHeight();
    
    //                     // Scale the image to fit the page
    //                     $scaleWidth = $imageWidth>0?$pageWidth / $imageWidth:0;
    //                     $scaleHeight = $imageHeight>0?$pageHeight / $imageHeight:0;
    //                     $scale = min($scaleWidth, $scaleHeight);
    
    //                     // Calculate new image dimensions
    //                     $scaledWidth = $imageWidth * $scale;
    //                     $scaledHeight = $imageHeight * $scale;
    
    //                     // Center the image on the page
    //                     $xPos = ($pageWidth - $scaledWidth) / 2;
    //                     $yPos = ($pageHeight - $scaledHeight) / 2;
    
    //                     // Add image to TCPDF
    //                     $tcpdf->Image($tmpImageFile, $xPos, $yPos, $scaledWidth, $scaledHeight, '', '', '', false);
    
    //                     // Clean up the temporary image file
    //                     unlink($tmpImageFile);
    //                 }
    //             }
    //         }
    //     }
    
    //     // Save the new PDF
    //     $outputFile = sys_get_temp_dir() . '/' . uniqid() . '_preprocessed.pdf';
    //     $tcpdf->Output($outputFile, 'F');
    //     print_r(" ");
    //     print_r($outputFile);die;
        
    //     return $outputFile;
    // }
    
    // private function preprocessPdf($pdfFile) {
    //     // Print the input PDF file path for debugging
    //     echo "Input PDF File: " . $pdfFile . "\n";
    
    //     // Create a new Pdf instance with the provided file
    //     try {

    //         $outputFile = sys_get_temp_dir() . '/' . uniqid() . '_preprocessed.pdf';


    //         $command = "pdftk $pdfFile output $outputFile";
    //         print_r($command);
    //         $output = shell_exec($command);
    //         // $pdf2 = new Pdf($pdfFile);
    
    //         print_r($output);
    //         // Generate a path for the preprocessed PDF
    
    //         print_r($outputFile);
    //         // Attempt to save the preprocessed PDF
    //         if ($output=="") {
    //             // echo "PDF successfully preprocessed and saved to: " . $outputFile . "\n";
    //             return $outputFile;
    //         } else {
    //             // print_r($outputFile);
    //             // If saving fails, output error and return null
    //             echo "Error preprocessing PDF: " . $output . "\n";
    //             return null;
    //         }
    //     } catch (Exception $e) {
    //         // Handle exceptions
    //         echo "Exception: " . $e->getMessage() . "\n";
    //         return null;
    //     }
    // }
    

    private function mergeSideBySide($pdfFile, $outputFile, $pageOrientation = 'L') {
        try {
            $pdfFile = $this->preprocessPdf($pdfFile);

            $pdf = new Fpdi();

            // Load the PDF file
            $pageCount = $pdf->setSourceFile($pdfFile);
    
            
            if ($pageCount < 1) {
                echo "No pages found in the PDF.";die;
            }
    
            // Landscape page dimensions
            $pdf->AddPage($pageOrientation);

            $spaceBetweenPages = 10; // 20px space between the two pages

            for ($i = 1; $i <= $pageCount; $i += 2) {
                $tplIdx1 = $pdf->importPage($i);
                $tplIdx2 = ($i + 1 <= $pageCount) ? $pdf->importPage($i + 1) : null;
    
                // Get size of the first page to determine scaling
                $size = $pdf->getTemplateSize($tplIdx1);
    
                // Calculate scale factor for landscape layout
                // $scale = ($pdf->GetPageWidth() / 2) / $size['width'];
                $scale = ($pdf->GetPageWidth() - $spaceBetweenPages) / 2 / $size['width'];

                // // First Page (left side)
                // $pdf->useTemplate($tplIdx1, 0, 0, $size['width'] * $scale, $size['height'] * $scale);
    
                // // Second Page (right side), if exists
                // if ($tplIdx2) {
                //     $pdf->useTemplate($tplIdx2, $pdf->GetPageWidth() / 2, 0, $size['width'] * $scale, $size['height'] * $scale);
                // }

                // First Page (left side)
                $pdf->useTemplate($tplIdx1, 0, 0, $size['width'] * $scale, $size['height'] * $scale);

                // Second Page (right side), if it exists
                if ($tplIdx2) {
                    $pdf->useTemplate($tplIdx2, ($pdf->GetPageWidth() / 2) + ($spaceBetweenPages / 2), 0, $size['width'] * $scale, $size['height'] * $scale);
                }
    
                // Add a new page only if there are more pages to process
                if ($i + 1 < $pageCount) {
                    $pdf->AddPage($pageOrientation);
                }
            }
    
            // Save the merged PDF
            $pdf->Output($outputFile, 'F');
    
            // Delete the preprocessed file after merging
            if (file_exists($pdfFile)) {
                // unlink($pdfFile);
            }
        } catch (Exception $e) {
            // Handle exceptions gracefully by logging or reporting the error
           echo "Error merging PDF: " . $e->getMessage();
            // Optionally, you can return an error message or flag
        }
    }

    private function mergeOneBelowOther($pdfFile, $outputFile, $pageOrientation) {
        $pdfFile = $this->preprocessPdf($pdfFile); // Pre-process the PDF

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($pdfFile);

        for ($i = 1; $i <= $pageCount; $i += 2) {
            $pdf->AddPage($pageOrientation);
            $tplIdx1 = $pdf->importPage($i);
            $tplIdx2 = ($i + 1 <= $pageCount) ? $pdf->importPage($i + 1) : null;

            $pdf->useTemplate($tplIdx1, 0, 0);

            if ($tplIdx2) {
                $pdf->AddPage($pageOrientation);
                $pdf->useTemplate($tplIdx2, 0, 0);
            }
        }

        $pdf->Output($outputFile, 'F');
        unlink($pdfFile); // Delete the preprocessed file after merging
    }

    private function processFolder($folderPath, $outputFolder, $pageOrientation, $mergeOption) {
        $pdfFiles = glob($folderPath . '/*.pdf');
        foreach ($pdfFiles as $pdfFile) {
            $outputFile = $outputFolder . '/' . basename($pdfFile, '.pdf') . '_merged.pdf';
            if ($mergeOption === 'side_by_side') {
                $this->mergeSideBySide($pdfFile, $outputFile, $pageOrientation);
            } else {
                $this->mergeOneBelowOther($pdfFile, $outputFile, $pageOrientation);
            }
        }
    }
}
