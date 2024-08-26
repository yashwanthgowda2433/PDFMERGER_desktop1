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
            throw new Exception("Error in preprocessing PDF: " . implode("\n", $output));
        }

        return $preprocessedFile;
    }
    
    private function mergeSideBySide($pdfFile, $outputFile, $pageOrientation = 'L', $space) {
        try {
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
            $spaceBetweenPages = $space; // 10px space between the two pages
    
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
    
                // Place the second page (right side) if it exists
                if ($tplIdx2) {
                    $pdf->useTemplate($tplIdx2, $halfPageWidth + $spaceBetweenPages, $y2, $halfPageWidth, $uniformHeight);
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
        $pdfFiles = glob($folderPath . '/*.pdf');
        foreach ($pdfFiles as $pdfFile) {
            $outputFile = $outputFolder . '/' . basename($pdfFile, '.pdf') . '_merged.pdf';
            if ($mergeOption === 'side_by_side') {
                $this->mergeSideBySide($pdfFile, $outputFile, $pageOrientation, $space);
            } else {
                $this->mergeOneBelowOther($pdfFile, $outputFile, $pageOrientation);
            }
        }
    }

    // PDFs End

    // Images Start
    public function merge_images() {
        $mainFolder = $this->input->post('directory');
        $outputFolder = $this->input->post('outputFolder');
        $space = $this->input->post('space');
    
        if (!is_dir($mainFolder)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid directory']);
            return;
        }
    
        $imageFiles = glob($mainFolder . '/*.{jpg,jpeg}', GLOB_BRACE);
        $totalFiles = count($imageFiles);
        if($totalFiles<1){
            $subFolders = glob($mainFolder. '*.{jpg,jpeg}', GLOB_ONLYDIR);
            $totalFiles = count($subFolders);

        }
        if ($totalFiles < 2) {
            echo json_encode(['status' => 'error', 'message' => 'Not enough images to merge']);
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
    
        for ($i = 0; $i < $totalFiles; $i += 2) {
            $image1 = $imageFiles[$i];
            $image2 = ($i + 1 < $totalFiles) ? $imageFiles[$i + 1] : null;
    
            $outputFile = $outputFolder . '/' . basename($image1, '.jpg') . '_merged.jpg';
            $this->mergeSideBySideImages($image1, $image2, $outputFile, $space);
    
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
    //     $spaceBetweenImages = $space;
    
    //     // Create a new image with combined width and max height
    //     $merged_width = $width1 + $width2 + $spaceBetweenImages;
    //     $merged_height = max($height1, $height2);
    //     $merged_image = imagecreatetruecolor($merged_width, $merged_height);
    
    //     // Fill the background with white color (optional)
    //     $white = imagecolorallocate($merged_image, 255, 255, 255);
    //     imagefill($merged_image, 0, 0, $white);
    
    //     // Copy the first image to the left side
    //     imagecopy($merged_image, $image1, 0, 0, 0, 0, $width1, $height1);
    
    //     // Copy the second image to the right side, if it exists
    //     if ($image2) {
    //         imagecopy($merged_image, $image2, $width1 + $spaceBetweenImages, 0, 0, 0, $width2, $height2);
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
    
        // Set the space between images
        $spaceBetweenImages = $space*2;
    
        // Calculate the equal width for both images
        $totalWidth = $width1 + $width2 + $spaceBetweenImages;
        $equalWidth = ($totalWidth - $spaceBetweenImages) / 2;
    
        // Calculate the scale factor to make both images equal in width
        $scale1 = $equalWidth / $width1;
        $scale2 = $equalWidth / $width2;
    
        // Calculate the new heights based on the scale factor
        $newHeight1 = $height1 * $scale1;
        $newHeight2 = $height2 * $scale2;
    
        // Create a new image with the combined width and max height
        $merged_height = max($newHeight1, $newHeight2);
        $merged_image = imagecreatetruecolor($equalWidth * 2 + $spaceBetweenImages, $merged_height);
    
        // Fill the background with white color (optional)
        $white = imagecolorallocate($merged_image, 255, 255, 255);
        imagefill($merged_image, 0, 0, $white);
    
        // Calculate the vertical positions to center the images if necessary
        $y1 = ($merged_height - $newHeight1) / 2;
        $y2 = ($merged_height - $newHeight2) / 2;
    
        // Resize and copy the first image to the left side
        $resizedImage1 = imagescale($image1, $equalWidth, $newHeight1);
        imagecopy($merged_image, $resizedImage1, 0, $y1, 0, 0, $equalWidth, $newHeight1);
        imagedestroy($resizedImage1);
    
        // Resize and copy the second image to the right side, if it exists
        if ($image2) {
            $resizedImage2 = imagescale($image2, $equalWidth, $newHeight2);
            imagecopy($merged_image, $resizedImage2, $equalWidth + $spaceBetweenImages, $y2, 0, 0, $equalWidth, $newHeight2);
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
        $imageFiles = glob($folderPath . '/*.jpg');
        if (empty($imageFiles)) {
            $imageFiles = glob($folderPath . '/*.jpeg');
        }
    
        foreach ($imageFiles as $imageFile) {
            $outputFile = $outputFolder . '/' . basename($imageFile, '.jpg') . '_merged.jpg';
            if ($mergeOption === 'side_by_side') {
                $this->mergeSideBySideImages($folderPath, $outputFile, $space);
            } else {
                $this->mergeOneBelowOtherImages($folderPath, $outputFile, $space);
            }
        }
    }
    
    // Images End

    // private function mergeSideBySide($pdfFile, $outputFile, $pageOrientation = 'L') {
    //     try {
    //         $pdfFile = $this->preprocessPdf($pdfFile);

    //         $pdf = new Fpdi();

    //         // Load the PDF file
    //         $pageCount = $pdf->setSourceFile($pdfFile);
    
            
    //         if ($pageCount < 1) {
    //             echo "No pages found in the PDF.";die;
    //         }
    
    //         // Landscape page dimensions
    //         $pdf->AddPage($pageOrientation);

    //         $spaceBetweenPages = 10; // 20px space between the two pages

    //         for ($i = 1; $i <= $pageCount; $i += 2) {
    //             $tplIdx1 = $pdf->importPage($i);
    //             $tplIdx2 = ($i + 1 <= $pageCount) ? $pdf->importPage($i + 1) : null;
    
    //             // Get size of the first page to determine scaling
    //             $size = $pdf->getTemplateSize($tplIdx1);
    
    //             // Calculate scale factor for landscape layout
    //             // $scale = ($pdf->GetPageWidth() / 2) / $size['width'];
    //             $scale = ($pdf->GetPageWidth() - $spaceBetweenPages) / 2 / $size['width'];

    //             // // First Page (left side)
    //             // $pdf->useTemplate($tplIdx1, 0, 0, $size['width'] * $scale, $size['height'] * $scale);
    
    //             // // Second Page (right side), if exists
    //             // if ($tplIdx2) {
    //             //     $pdf->useTemplate($tplIdx2, $pdf->GetPageWidth() / 2, 0, $size['width'] * $scale, $size['height'] * $scale);
    //             // }

    //             // First Page (left side)
    //             $pdf->useTemplate($tplIdx1, 0, 0, $size['width'] * $scale, $size['height'] * $scale);

    //             // Second Page (right side), if it exists
    //             if ($tplIdx2) {
    //                 $pdf->useTemplate($tplIdx2, ($pdf->GetPageWidth() / 2) + ($spaceBetweenPages / 2), 0, $size['width'] * $scale, $size['height'] * $scale);
    //             }
    
    //             // Add a new page only if there are more pages to process
    //             if ($i + 1 < $pageCount) {
    //                 $pdf->AddPage($pageOrientation);
    //             }
    //         }
    
    //         // Save the merged PDF
    //         $pdf->Output($outputFile, 'F');
    
    //         // Delete the preprocessed file after merging
    //         if (file_exists($pdfFile)) {
    //             // unlink($pdfFile);
    //         }
    //     } catch (Exception $e) {
    //         // Handle exceptions gracefully by logging or reporting the error
    //        echo "Error merging PDF: " . $e->getMessage();
    //         // Optionally, you can return an error message or flag
    //     }
    // }

    // private function mergeSideBySide($pdfFile, $outputFile, $pageOrientation = 'L') {
    //     try {
    //         $pdfFile = $this->preprocessPdf($pdfFile);
    
    //         $pdf = new Fpdi();
    
    //         // Load the PDF file
    //         $pageCount = $pdf->setSourceFile($pdfFile);
    
    //         if ($pageCount < 1) {
    //             echo "No pages found in the PDF.";
    //             die;
    //         }
    
    //         // Set page orientation and dimensions
    //         $pdf->AddPage($pageOrientation);
    //         $spaceBetweenPages = 10; // 10px space between the two pages
    
    //         for ($i = 1; $i <= $pageCount; $i += 2) {
    //             $tplIdx1 = $pdf->importPage($i);
    //             $tplIdx2 = ($i + 1 <= $pageCount) ? $pdf->importPage($i + 1) : null;
    
    //             // Get the size of the first page to determine scaling
    //             $size1 = $pdf->getTemplateSize($tplIdx1);
    //             $size2 = ($tplIdx2) ? $pdf->getTemplateSize($tplIdx2) : null;
    
    //             // Calculate the width of each half-page, subtracting space for the gap
    //             $halfPageWidth = ($pdf->GetPageWidth() - $spaceBetweenPages) / 2;
    
    //             // Scale factor to fit the width
    //             $scale1 = $halfPageWidth / $size1['width'];
    //             $scale2 = ($size2) ? $halfPageWidth / $size2['width'] : null;
    
    //             // Adjust the height to maintain aspect ratio
    //             $newHeight1 = $size1['height'] * $scale1;
    //             $newHeight2 = ($scale2) ? $size2['height'] * $scale2 : 0;
    
    //             // If height is less than the page height, center it vertically
    //             $y1 = max(($pdf->GetPageHeight() - $newHeight1) / 2, 0);
    //             $y2 = max(($pdf->GetPageHeight() - $newHeight2) / 2, 0);
    
    //             // Place the first page (left side) scaled to 100% width
    //             $pdf->useTemplate($tplIdx1, 0, $y1, $halfPageWidth, $newHeight1);
    
    //             // Place the second page (right side) scaled to 100% width if it exists
    //             if ($tplIdx2) {
    //                 $pdf->useTemplate($tplIdx2, $halfPageWidth + $spaceBetweenPages, $y2, $halfPageWidth, $newHeight2);
    //             }
    
    //             // Add a new page only if there are more pages to process
    //             if ($i + 1 < $pageCount) {
    //                 $pdf->AddPage($pageOrientation);
    //             }
    //         }
    
    //         // Save the merged PDF
    //         $pdf->Output($outputFile, 'F');
    
    //         // Delete the preprocessed file after merging
    //         if (file_exists($pdfFile)) {
    //             unlink($pdfFile);
    //         }
    //     } catch (Exception $e) {
    //         // Handle exceptions gracefully by logging or reporting the error
    //         echo "Error merging PDF: " . $e->getMessage();
    //         // Optionally, you can return an error message or flag
    //     }
    // }
}
