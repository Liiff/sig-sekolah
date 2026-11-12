<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;


class SekolahController extends Controller
{
    private function jarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000; // jari-jari bumi (meter)
        $p1 = deg2rad($lat1);
        $p2 = deg2rad($lat2);
        $dp = deg2rad($lat2 - $lat1);
        $dl = deg2rad($lng2 - $lng1);

        $a = sin($dp / 2) ** 2 + cos($p1) * cos($p2) * sin($dl / 2) ** 2;

        return 2 * $r * asin(min(1, sqrt($a)));
    }

    public function index(Request $request)
    {
        $sekolahs = Sekolah::query()
            ->when($request->q, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('nama', 'like', "%{$q}%")
                        ->orWhere('npsn', 'like', "%{$q}%")
                        ->orWhere('kecamatan', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('admin.sekolah.index', compact('sekolahs'));
    }

    public function create()
    {
        return view('admin.sekolah.create');
    }

    public function store(Request $request)
    {
        Sekolah::create($request->validate($this->rules()));

        return redirect()->route('dinas.sekolah.index')
            ->with('success', 'Data sekolah berhasil ditambahkan.');
    }

    public function edit(Sekolah $sekolah)
    {
        Gate::authorize('update', $sekolah);

        return view('admin.sekolah.edit', compact('sekolah'));
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        Gate::authorize('update', $sekolah);

        $data = $request->validate($this->rules($sekolah->id));

        // NPSN adalah identitas resmi: tidak boleh diubah oleh sekolah
        if ($request->user()->isSekolah()) {
            unset($data['npsn']);

            $jarak = $this->jarakMeter(
                (float) $sekolah->latitude, (float) $sekolah->longitude,
                (float) $data['latitude'], (float) $data['longitude']
            );

            if ($jarak > Sekolah::BATAS_GESER_METER) {
                return back()->withInput()->withErrors([
                    'latitude' => 'Lokasi baru berjarak ' . round($jarak)
                        . ' m dari titik semula (batas ' . Sekolah::BATAS_GESER_METER
                        . ' m). Hubungi Dinas Pendidikan untuk perubahan lebih jauh.',
                ]);
            }
        }

        $sekolah->update($data);

        return $this->kembali($request)
            ->with('success', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        $sekolah->delete();

        return redirect()->route('dinas.sekolah.index')
            ->with('success', 'Data sekolah berhasil dihapus.');
    }

    private function kembali(Request $request)
    {
        return $request->user()->isDinas()
            ? redirect()->route('dinas.sekolah.index')
            : redirect()->route('dashboard');
    }

    private function rules(?int $id = null): array
    {
        return [
            'npsn' => [
                'required',
                'digits:8',
                Rule::unique('sekolahs', 'npsn')->ignore($id),
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
            ],
            'jenjang' => [
                'required',
                Rule::in(['SD', 'SMP', 'SMA', 'SMK']),
            ],
            'status' => [
                'required',
                Rule::in(['Negeri', 'Swasta']),
            ],
            'alamat' => [
                'required',
                'string',
            ],
            'kecamatan' => [
                'required',
                'string',
                'max:100',
            ],
            'telepon' => [
                'nullable',
                'string',
                'max:20',
            ],
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
        ];
    }
}
