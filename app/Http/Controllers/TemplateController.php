<?php

namespace App\Http\Controllers;

use App\Models\PerjadinDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Response;

class TemplateController extends Controller
{
    public function sptjmDocx($id)
    {
        $result = PerjadinDetail::where('id', $id)->with('master')->first();
        try {
            $templateProcessor = new TemplateProcessor(storage_path('app\public\template\template_sptjm.docx'));
            $templateProcessor->setValue('nama', $result->nama);
            $templateProcessor->setValue('nip', $result->nip);
            $templateProcessor->setValue('jabatan', $result->jabatan);
            $templateProcessor->setValue('no_st', $result->master->no_st);
            $templateProcessor->setValue('tanggal_st', Carbon::parse($result->master->tanggal_st)->format('d F Y'));
            $templateProcessor->saveAs(storage_path('app\public\perjadin\ptj\lainnya\sptjm\sptjm_' . $result->id . '.docx'));

            return Response::download(storage_path('app\public\perjadin\ptj\lainnya\sptjm\sptjm_' . $result->id . '.docx'), 'sptjm_' . $result->no_sppd . '.docx');
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

        return response()->download(public_path('uploads/' . $fileName));
    }
}
