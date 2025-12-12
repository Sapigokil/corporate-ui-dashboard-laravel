<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan Anda mengimpor Facade DomPDF

class PdfController extends Controller
{
    /**
     * Menghasilkan file PDF dari view.
     *
     * @return \Illuminate\Http\Response
     */
    public function generatePdf()
    {
        // 1. Ambil data (jika ada, contoh: $data = User::all())
        $data = [
            'title' => 'Laporan Uji Coba Dompdf',
            'date' => date('m/d/Y'),
        ];
        
        // 2. Load view yang sudah dibuat
        $pdf = Pdf::loadView('pdf.test_pdf', $data);
        
        // 3. Kembalikan PDF untuk diunduh (download) atau ditampilkan (stream)
        
        // Mengunduh file dengan nama 'laporan-corporate.pdf'
        return $pdf->stream('laporan-corporate.pdf'); 
        
        // ATAU (Untuk menampilkan di browser)
        // return $pdf->stream('laporan-corporate.pdf'); 
    }
}