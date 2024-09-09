<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpWord\TemplateProcessor;

class TemplateController extends Controller
{
    public function generateDocx()
    {
        try {
            Storage::makeDirectory('public/keuangan/2024/sppd');
            $templateProcessor = new TemplateProcessor(storage_path('app/public/template/template_kuitansi.docx'));
            $templateProcessor->setValue('tahun_anggaran', '2024');

            $templateProcessor->saveAs(storage_path('app/public/keuangan/2024/sppd/hasil.docx'));

            $domPdfPath = base_path('vendor/dompdf/dompdf');
            \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
            \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');
            $Content = \PhpOffice\PhpWord\IOFactory::load(storage_path('app/public/keuangan/2024/sppd/hasil.docx'));
            $PDFWriter = \PhpOffice\PhpWord\IOFactory::createWriter($Content, 'PDF');
    //                $PDFWriter->save('php://output');
    //  $PDFWriter->save('c:/temp/test.pdf');

            $pdfFileName = 'doc_' . time() . '.pdf';
            $PDFWriter->save(storage_path('app/public/keuangan/2024/sppd/'.$pdfFileName));
        } catch (Exception $e) {
            return $e->getMessage();
        }

        // return response()->download(storage_path('template.docx'));
    }

    public function index(): View
    {
        return view('word-pdf');
    }
    public function store(Request $request)
    {
        $fileName = 'doc_' . time() . '.' . $request->file->extension();
        $request->file->move(public_path('uploads'), $fileName);

        return response()->download(public_path('uploads/' . $pdfFileName));
    }
}
