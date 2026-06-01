@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('guru.tugas.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Daftar Tugas
            </a>
        </div>

        <!-- Tugas Detail Card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $tugas->judul_tugas }}</h1>
                    <div class="flex gap-4 text-sm text-gray-600">
                        <span class="font-semibold">Kelas: {{ $tugas->kelas->nama_kelas ?? 'N/A' }}</span>
                        <span class="font-semibold">Mapel: {{ $tugas->mapel->nama_mapel ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('guru.tugas.update', $tugas->id_tugas) }}"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Edit
                    </a>
                    <form action="{{ route('guru.tugas.destroy', $tugas->id_tugas) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>

            <!-- Info Section -->
            <div class="grid grid-cols-2 gap-4 mb-6 pb-6 border-b">
                <div>
                    <p class="text-gray-600 text-sm">Tanggal Mulai</p>
                    <p class="font-semibold text-gray-900">
                        {{ \Carbon\Carbon::parse($tugas->tanggal_mulai)->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Tanggal Deadline</p>
                    <p class="font-semibold text-gray-900">
                        {{ \Carbon\Carbon::parse($tugas->tanggal_deadline)->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Status</p>
                    <p class="font-semibold">
                        <span class="px-3 py-1 rounded-full text-white bg-green-600">{{ ucfirst($tugas->status) }}</span>
                    </p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Bobot Nilai</p>
                    <p class="font-semibold text-gray-900">{{ $tugas->bobot_nilai }}%</p>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Deskripsi Tugas</h2>
                <div class="text-gray-700 whitespace-pre-wrap bg-gray-50 p-4 rounded">
                    {!! nl2br(e($tugas->deskripsi)) !!}
                </div>
            </div>

            <!-- File Section -->
            @if ($tugas->file_path)
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">File Tugas</h2>
                    <a href="{{ Storage::url($tugas->file_path) }}" target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Download: {{ $tugas->file_name }}
                    </a>
                </div>
            @endif

            <!-- Action Button -->
            <div class="pt-6 border-t">
                <a href="{{ route('guru.tugas.pengumpulan', $tugas->id_tugas) }}"
                    class="px-6 py-3 bg-indigo-600 text-white rounded hover:bg-indigo-700 inline-block">
                    Lihat Pengumpulan Siswa
                </a>
            </div>
        </div>
    </div>
@endsection
