<!DOCTYPE html>
<html lang="en">

<head>
    <title>Agenda Pimpinan</title>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet" />
</head>


<body>
    <div class="px-6 py-6 text-center">
        <h1 class="text-3xl font-bold">Agenda Pimpinan</h1>
    </div>

    <div class="px-6">
        <div class="">
            <p class="">Tanggal Data : <span class="gray-color">{{ $fromDate }} -
                    {{ $toDate }} </span></p>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div class="relative overflow-x-auto p-6 m-4 shadow-lg rounded-lg bg-gray-100">
          <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-center text-xs text-gray-200 uppercase bg-gray-500 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th style="width:8%">Tanggal</th>
                    <th style="width:10%">Inspektur Jenderal</th>
                    <th style="width:10%">Sekretaris Inspektorat Jenderal</th>
                    <th style="width:10%">Inspektur Wilayah I</th>
                    <th style="width:10%">Inspektur Wilayah II</th>
                    <th style="width:10%">Inspektur Wilayah III</th>
                    <th style="width:10%">Inspektur Wilayah IV</th>
                    <th style="width:10%">Inspektur Wilayah V</th>
                    <th style="width:10%">Inspektur Wilayah VI</th>
                </tr>
            </thead>
            <tbody>
                @foreach (json_decode($data) as $key => $item)
                     <tr class="border-b dark:border-gray-700">
                        <td>{{ $item->tanggal }}</td>
                        @foreach ($item->detail as $pim)
                            <td class="px-1">
                                @if (count($pim->kegiatan) > 0)
                                <div class="flex flex-col space-y-4">
                                     @foreach ($pim->kegiatan as $key => $kegiatan)
                                       <div class="border-b-2"><span> {{$key + 1}}</span>
                                            {{ $kegiatan->kegiatan }} di {{ $kegiatan->tempat }} pada jam
                                                {{ $kegiatan->jam_mulai }} - {{ $kegiatan->jam_akhir }}
                                                </div>
                                        @endforeach
                                </div>
                                    {{-- <ol class="mt-2 list-inside list-decimal space-y-1 ps-5 justify-start">

                                       
                                    </ol> --}}
                                @else
                                    
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>

</html>
