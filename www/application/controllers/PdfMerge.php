<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set('memory_limit', '1024M');

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

    // PDFS start
    public function merge_pdfs() {
        $mainFolder = $this->input->post('directory');
        $outputFolder = $this->input->post('outputFolder');
        $pageOrientation = $this->input->post('pageOrientation');
        $mergeOption = $this->input->post('mergeOption');
        $space = $this->input->post('space');

        if (!is_dir($mainFolder)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid directory']);
            return;
        }

        $subFolders = glob($mainFolder . '/*', GLOB_ONLYDIR);
        $totalFiles = count($subFolders);
        if($totalFiles<1){
            $subFolders = glob($mainFolder, GLOB_ONLYDIR);
            $totalFiles = count($subFolders);

        }
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
            $this->processFolder($subFolder, $outputFolder, $pageOrientation, $mergeOption, $space);
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
            // throw new Exception("Error in preprocessing PDF: " . implode("\n", $output));
        }

        return $preprocessedFile;
    }
    
    private function mergeSideBySide($pdfFile, $outputFile, $pageOrientation = 'L', $space) {
        try {
            $root_file = $pdfFile;
            $pdfFile = $this->preprocessPdf($pdfFile);
            $pdf = new Fpdi();
    
            // Load the PDF file
            $pageCount = $pdf->setSourceFile($pdfFile);
    
            if ($pageCount < 1) {
                echo "No pages found in the PDF.";
                die;
            }
    
            // Set page orientation and dimensions
            $pdf->AddPage($pageOrientation);
            $spaceBetweenPages = $space?$space:0; // 10px space between the two pages
    
            // Calculate the dimensions for all pages
            $maxWidth = 0;
            $maxHeight = 0;
    
            for ($i = 1; $i <= $pageCount; $i++) {
                $size = $pdf->getTemplateSize($pdf->importPage($i));
                if ($size['width'] > $maxWidth) {
                    $maxWidth = $size['width'];
                }
                if ($size['height'] > $maxHeight) {
                    $maxHeight = $size['height'];
                }
            }
    
            $halfPageWidth = ($pdf->GetPageWidth() - $spaceBetweenPages) / 2;
            $scaleFactor = $halfPageWidth / $maxWidth;
            $uniformHeight = $maxHeight * $scaleFactor;
    
            for ($i = 1; $i <= $pageCount; $i += 2) {
                $tplIdx1 = $pdf->importPage($i);

                $tplIdx2 = ($i + 1 <= $pageCount) ? $pdf->importPage($i + 1) : null;
    
                // Calculate uniform Y positions to center vertically
                $y1 = max(($pdf->GetPageHeight() - $uniformHeight) / 2, 0);
                $y2 = $y1; // Same Y position for both pages
    
                // Place the first page (left side)
                $pdf->useTemplate($tplIdx1, 0, $y1, $halfPageWidth, $uniformHeight);
                // print_r($tplIdx1.', '.(0).', '.$y1.', '.$halfPageWidth.', '.$uniformHeight);
    
                // Place the second page (right side) if it exists
                if ($tplIdx2) {
                    $pdf->useTemplate($tplIdx2, $halfPageWidth + $spaceBetweenPages, $y2, $halfPageWidth, $uniformHeight);
                }else{
                    $directory = dirname($root_file);
                    $pdfFiles = glob($directory . '/*.pdf');
                    // Sort files by name
                    usort($pdfFiles, function($a, $b) {
                        return strcmp(basename($a), basename($b));
                    });
                    $currentIndex = array_search($root_file, $pdfFiles);
                    if ($currentIndex === false || $currentIndex + 1 >= count($pdfFiles)) {
                        // echo 'failed';die;
                        
                    }else{
                        $nextPdfFile = $pdfFiles[$currentIndex + 1];
                        $nextPdfFile = $this->preprocessPdf($nextPdfFile);

                        // $pdfNext = new Fpdi();
                        $pageCountNext = $pdf->setSourceFile($nextPdfFile);
            
                        if ($pageCountNext < 1) {
                            // throw new Exception("No pages found in the next PDF.");
                        }
            
                        $tplIdxNext = $pdf->importPage(1);
                        if (!$tplIdxNext) {
                            // throw new Exception("Failed to import page 1 from next PDF.");
                        }
                        // print_r("/n");
                        // print_r($tplIdxNext.', '.$halfPageWidth.', '.$spaceBetweenPages.', '.$y2.', '.$halfPageWidth.', '.$uniformHeight);die;

                        // Add a new page to the current PDF for the next PDF's first page
                        // $pdfNext->AddPage($pageOrientation);
                        $pdf->useTemplate($tplIdxNext, $halfPageWidth + $spaceBetweenPages, $y2, $halfPageWidth, $uniformHeight);
                        
                        // if (file_exists($nextPdfFile)) {
                        //     unlink($nextPdfFile); // Clean up temporary file
                        // }
                    }
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
                unlink($pdfFile);
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

    private function processFolder($folderPath, $outputFolder, $pageOrientation, $mergeOption, $space) {
        $lastDirName = basename(rtrim($folderPath, '/\\'));
         
        // Check if the $lastDirName folder exists inside the $outputFile folder
        $outputDir = rtrim($outputFolder, '/') . '/' . $lastDirName;
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $pdfFiles = glob($folderPath . '/*.pdf');
        foreach ($pdfFiles as $pdfFile) {

           

            // Save the merged PDF inside the $lastDirName folder
            // $outputFilePath = $outputDir . '/' . basename($outputFile);

            $outputFile = $outputDir . '/' . basename($pdfFile, '.pdf') . '.pdf';
            if ($mergeOption === 'side_by_side') {
                $this->mergeSideBySide($pdfFile, $outputFile, $pageOrientation, $space);
            } else {
                $this->mergeOneBelowOther($pdfFile, $outputFile, $pageOrientation);
            }
        }
    }




    private function getFirstPage($pdfFile) {
        $pdf = new Fpdi();
        $pdf->setSourceFile($this->preprocessPdf($pdfFile));
        return $pdf->importPage(1);
    }
    
    // PDFs End

    // Images Start
    public function merge_images() {
        $mainFolder = $this->input->post('directory');
        $outputFolder = $this->input->post('outputFolder');
        $mergeOption = $this->input->post('mergeOption'); // Added to handle side_by_side or one_below_other merging
        $space = $this->input->post('space');
    
        if (!is_dir($mainFolder)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid directory']);
            return;
        }
    
        $subFolders = glob($mainFolder . '/*', GLOB_ONLYDIR);
        $totalFiles = count($subFolders);
        if($totalFiles<1){
            $subFolders = glob($mainFolder, GLOB_ONLYDIR);
            $totalFiles = count($subFolders);

        }
        if ($totalFiles < 1) {
            echo json_encode(['status' => 'error', 'message' => 'No subfolders found']);
            return;
        }
    
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
            $this->processImageFolder($subFolder, $outputFolder, $mergeOption, $space);
            $processed++;
            $percentage = round(($processed / $totalFiles) * 100);
    
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
    
    // private function mergeSideBySideImages($image1Path, $image2Path, $outputFile, $space) {
    //     // Load the first image
    //     $image1 = imagecreatefromjpeg($image1Path);
    //     $width1 = imagesx($image1);
    //     $height1 = imagesy($image1);
    
    //     // Load the second image if it exists
    //     if ($image2Path) {
    //         $image2 = imagecreatefromjpeg($image2Path);
    //         $width2 = imagesx($image2);
    //         $height2 = imagesy($image2);
    //     } else {
    //         $image2 = null;
    //         $width2 = 0;
    //         $height2 = 0;
    //     }
    
    //     // Set the space between images
    //     $spaceBetweenImages = $space*2;
    
    //     // Calculate the equal width for both images
    //     $totalWidth = $width1 + $width2 + $spaceBetweenImages;
    //     $equalWidth = ($totalWidth - $spaceBetweenImages) / 2;
    
    //     // Calculate the scale factor to make both images equal in width
    //     $scale1 = $equalWidth / $width1;
    //     $scale2 = $equalWidth / $width2;
    
    //     // Calculate the new heights based on the scale factor
    //     $newHeight1 = $height1 * $scale1;
    //     $newHeight2 = $height2 * $scale2;
    
    //     // Create a new image with the combined width and max height
    //     $merged_height = max($newHeight1, $newHeight2);
    //     $merged_image = imagecreatetruecolor($equalWidth * 2 + $spaceBetweenImages, $merged_height);
    
    //     // Fill the background with white color (optional)
    //     $white = imagecolorallocate($merged_image, 255, 255, 255);
    //     imagefill($merged_image, 0, 0, $white);
    
    //     // Calculate the vertical positions to center the images if necessary
    //     $y1 = ($merged_height - $newHeight1) / 2;
    //     $y2 = ($merged_height - $newHeight2) / 2;
    
    //     // Resize and copy the first image to the left side
    //     $resizedImage1 = imagescale($image1, $equalWidth, $newHeight1);
    //     imagecopy($merged_image, $resizedImage1, 0, $y1, 0, 0, $equalWidth, $newHeight1);
    //     imagedestroy($resizedImage1);
    
    //     // Resize and copy the second image to the right side, if it exists
    //     if ($image2) {
    //         $resizedImage2 = imagescale($image2, $equalWidth, $newHeight2);
    //         imagecopy($merged_image, $resizedImage2, $equalWidth + $spaceBetweenImages, $y2, 0, 0, $equalWidth, $newHeight2);
    //         imagedestroy($resizedImage2);
    //         imagedestroy($image2); // Free memory for the second image
    //     }
    
    //     // Save the merged image
    //     imagejpeg($merged_image, $outputFile);
    
    //     // Free memory
    //     imagedestroy($image1);
    //     imagedestroy($merged_image);
    // }

    private function mergeSideBySideImages($image1Path, $image2Path, $outputFile, $space) {
        // Load the first image
        $image1 = imagecreatefromjpeg($image1Path);
        $width1 = imagesx($image1);
        $height1 = imagesy($image1);
    
        // Load the second image if it exists
        if ($image2Path) {
            $image2 = imagecreatefromjpeg($image2Path);
            $width2 = imagesx($image2);
            $height2 = imagesy($image2);
        } else {
            $image2 = null;
            $width2 = 0;
            $height2 = 0;
        }
    
        // A3 landscape dimensions (420mm x 297mm in pixels, assuming 300 DPI)
        $a3_width = 4961; // 420mm * 300 DPI
        $a3_height = 3508; // 297mm * 300 DPI
    
        // Calculate the target width for each image to take 50% of the total width minus the space
        $targetWidth = ($a3_width - $space) / 2;
    
        // Calculate the scale factors to resize images to the target width
        $scale1 = $targetWidth / $width1;
        $scale2 = $targetWidth / $width2;
    
        // Calculate the new heights based on the scale factors
        $newHeight1 = $height1 * $scale1;
        $newHeight2 = $height2 * $scale2;
    
        // Create a new image with A3 dimensions
        $merged_image = imagecreatetruecolor($a3_width, $a3_height);
    
        // Fill the background with white color
        $white = imagecolorallocate($merged_image, 255, 255, 255);
        imagefill($merged_image, 0, 0, $white);
    
        // Calculate the vertical positions to center the images on the A3 page
        $y1 = ($a3_height - $newHeight1) / 2;
        $y2 = ($a3_height - $newHeight2) / 2;
    
        // Resize and copy the first image to the left side
        $resizedImage1 = imagescale($image1, $targetWidth, $newHeight1);
        imagecopy($merged_image, $resizedImage1, 0, $y1, 0, 0, $targetWidth, $newHeight1);
        imagedestroy($resizedImage1);
    
        // Resize and copy the second image to the right side, if it exists
        if ($image2) {
            $resizedImage2 = imagescale($image2, $targetWidth, $newHeight2);
            imagecopy($merged_image, $resizedImage2, $targetWidth + $space, $y2, 0, 0, $targetWidth, $newHeight2);
            imagedestroy($resizedImage2);
            imagedestroy($image2); // Free memory for the second image
        }
    
        // Save the merged image
        imagejpeg($merged_image, $outputFile);
    
        // Free memory
        imagedestroy($image1);
        imagedestroy($merged_image);
    }
    
    
    
    private function mergeOneBelowOtherImages($imageFile, $outputFile, $space) {
        $images = glob($imageFile . '/*.jpg');
        if (empty($images)) {
            $images = glob($imageFile . '/*.jpeg');
        }
    
        $totalImages = count($images);
        if ($totalImages < 2) {
            return; // Need at least two images to merge
        }
    
        $image1 = imagecreatefromjpeg($images[0]);
        $image2 = imagecreatefromjpeg($images[1]);
    
        $width1 = imagesx($image1);
        $height1 = imagesy($image1);
        $width2 = imagesx($image2);
        $height2 = imagesy($image2);
    
        $merged_width = max($width1, $width2);
        $merged_height = $height1 + $height2 + $space;
    
        $merged_image = imagecreatetruecolor($merged_width, $merged_height);
        $white = imagecolorallocate($merged_image, 255, 255, 255);
        imagefill($merged_image, 0, 0, $white);
    
        imagecopy($merged_image, $image1, 0, 0, 0, 0, $width1, $height1);
        imagecopy($merged_image, $image2, 0, $height1 + $space, 0, 0, $width2, $height2);
    
        imagejpeg($merged_image, $outputFile);
    
        imagedestroy($image1);
        imagedestroy($image2);
        imagedestroy($merged_image);
    }
    
    private function processImageFolder($folderPath, $outputFolder, $mergeOption, $space) {
        $lastDirName = basename(rtrim($folderPath, '/\\'));
        
        // Check if the $lastDirName folder exists inside the $outputFile folder
        $outputDir = rtrim($outputFolder, '/') . '/' . $lastDirName;
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
    
        $imageFiles = glob($folderPath . '/*.{jpg,jpeg}', GLOB_BRACE);
        $totalFiles = count($imageFiles);
        $processed = 0;
        for ($i = 0; $i < $totalFiles; $i += 2) {
            $image1 = $imageFiles[$i];
            $image2 = ($i + 1 < $totalFiles) ? $imageFiles[$i + 1] : null;
    
            $outputFile = $outputDir . '/' . basename($image1, '.jpg') .'_'. basename($image2, '.jpg') . '.jpg';
            if ($mergeOption === 'side_by_side') {
                $this->mergeSideBySideImages($image1, $image2, $outputFile, $space);
            } else {
                $this->mergeOneBelowOtherImages($image1, $image2, $outputFile, $space);
            }

            $percentage = round(($processed / $totalFiles) * 100);
            $processed++;
            echo json_encode(['status' => 'processing', 'progress' => $percentage]) . "\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }
    }
    
    // Images End

    // Images to PDFS start
    public function merge_images_pdf() {
        $mainFolder = $this->input->post('directory');
        $outputFolder = $this->input->post('outputFolder');
        $mergeOption = $this->input->post('mergeOption'); // Added to handle side_by_side or one_below_other merging
        $space = $this->input->post('space');
    
        if (!is_dir($mainFolder)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid directory']);
            return;
        }
    
        $subFolders = glob($mainFolder . '/*', GLOB_ONLYDIR);
        $totalFiles = count($subFolders);
        if($totalFiles<1){
            $subFolders = glob($mainFolder, GLOB_ONLYDIR);
            $totalFiles = count($subFolders);

        }
        if ($totalFiles < 1) {
            echo json_encode(['status' => 'error', 'message' => 'No subfolders found']);
            return;
        }
    
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
            $this->processImageFolderToPdf($subFolder, $outputFolder, $mergeOption, $space);
            $processed++;
            $percentage = round(($processed / $totalFiles) * 100);
    
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

    private function processImageFolderToPdf($folderPath, $outputFolder, $mergeOption, $space) {
        $lastDirName = basename(rtrim($folderPath, '/\\'));
        
        // Check if the $lastDirName folder exists inside the $outputFile folder
        $outputDir = rtrim($outputFolder, '/') . '/' . $lastDirName;
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
    
        $imageFiles = glob($folderPath . '/*.{jpg,jpeg}', GLOB_BRACE);
        $totalFiles = count($imageFiles);
        $mergedImages = [];

        for ($i = 0; $i < $totalFiles; $i += 2) {
            $image1 = $imageFiles[$i];
            $image2 = ($i + 1 < $totalFiles) ? $imageFiles[$i + 1] : null;
    
            $outputFile = $outputDir . '/' . basename($image1, '.jpg') .'_'. basename($image2, '.jpg') . '.jpg';
            if ($mergeOption === 'side_by_side') {
                $this->mergeSideBySideImages($image1, $image2, $outputFile, $space);
            } else {
                $this->mergeOneBelowOtherImages($image1, $image2, $outputFile, $space);
            }
            $mergedImages[] = $outputFile;
        }
        // After merging images, convert them to a PDF
        $this->convertImagesToPdf($mergedImages, $outputDir . '/' . $lastDirName . '.pdf');
    }

    private function convertImagesToPdf($imageFiles, $outputPdfPath) {
        // Assuming you have the FPDF library or any other PDF generation library installed
        $pdf = new FPDF();
    
        foreach ($imageFiles as $image) {
            $pdf->AddPage('L');
            $pdf->Image($image, 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
        }
    
        $pdf->Output($outputPdfPath, 'F');
    }
    // Images to PDFS End
   
}

?>