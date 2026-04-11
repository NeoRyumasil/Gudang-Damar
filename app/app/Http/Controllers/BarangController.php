<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarangController extends Controller
{
    public function index()
    {
        return Inertia::render('barang/Index', [
            'barangList' => Barang::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('barang/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'               => 'required|string',
            'harga.harga'        => 'required|integer|min:0',
            'harga.jumlah'       => 'required|integer|min:1',
            'kategori.ukuran'    => 'required|integer',
            'kategori.bentuk'    => 'required|string',
            'kategori.ketebalan' => 'required|string',
            'kategori.bahan'     => 'required|string',
            'kategori.merek'     => 'required|string',
        ]);

        $lastId = Barang::max('id_barang') ?? 0;

        Barang::create([
            'id_barang'  => $lastId + 1,
            'nama'       => $request->nama,
            'harga'      => $request->input('harga.harga'),
            'jumlah'     => $request->input('harga.jumlah'),
            'total'      => $request->input('harga.harga') * $request->input('harga.jumlah'),
            'ukuran'     => $request->input('kategori.ukuran'),
            'bentuk'     => $request->input('kategori.bentuk'),
            'ketebalan'  => $request->input('kategori.ketebalan'),
            'bahan'      => $request->input('kategori.bahan'),
            'guna_merek' => $request->input('kategori.merek'),
        ]);

        return redirect()->route('barang.index')
                         ->with('success', 'Barang berhasil ditambahkan!');
    }

    public function show(string $id)
    {
        return Inertia::render('barang/Detail', [
            'barang' => Barang::findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('barang/Edit', [
            'barang' => Barang::findOrFail($id),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama'               => 'required|string',
            'harga.harga'        => 'required|integer|min:0',
            'kategori.ukuran'    => 'required|integer',
            'kategori.ketebalan' => 'required|string',
            'kategori.bahan'     => 'required|string',
        ]);

        $barang = Barang::findOrFail($id);

        $barang->update([
            'nama'      => $request->nama,
            'harga'     => $request->input('harga.harga'),
            'total'     => $request->input('harga.harga') * $barang->jumlah,
            'ukuran'    => $request->input('kategori.ukuran'),
            'ketebalan' => $request->input('kategori.ketebalan'),
            'bahan'     => $request->input('kategori.bahan'),
        ]);

        return redirect()->route('barang.show', $id)
                         ->with('success', 'Barang berhasil diupdate!');
    }

    public function destroy(string $id)
    {
        Barang::findOrFail($id)->delete();

        return redirect()->route('barang.index')
                         ->with('success', 'Barang berhasil dihapus!');
    }
}